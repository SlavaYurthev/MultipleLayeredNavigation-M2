<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\Layer\Filter;

class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price {
	const PRICE_DELTA = 0.01;
	protected $dataProvider;
	protected $urlBuilder;
	protected $collectionProvider;
	protected $emptyCollection;
	protected $priceCurrency;
	public function __construct(
		\Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Layer $layer,
		\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
		\Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		\Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
		\Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
		\SY\MultipleLayeredNavigation\Model\Url\Builder $urlBuilder,
		\SY\MultipleLayeredNavigation\Model\Layer\ItemCollectionProvider $collectionProvider,
		array $data = []
	) {
		parent::__construct(
			$filterItemFactory,
			$storeManager,
			$layer,
			$itemDataBuilder,
			$resource,
			$customerSession,
			$priceAlgorithm,
			$priceCurrency,
			$algorithmFactory,
			$dataProviderFactory,
			$data
		);
		$this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
		$this->urlBuilder = $urlBuilder;
		$this->collectionProvider = $collectionProvider;
		$this->priceCurrency = $priceCurrency;
	}
	public function apply(\Magento\Framework\App\RequestInterface $request){
		$this->applyToCollection($this->getLayer()->getProductCollection(), true);
		return $this;
	}
	public function applyToCollection($collection, $addFilter = false){
		$values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		if (!$values){
			return $this;
		}
		$ranges = [];
		if($addFilter) {
			foreach ($values as $value) {
				list($from, $to) = explode("-", $value);
				$label = $this->_renderRangeLabel($from, $to);
				$this->getLayer()->getState()->addFilter($this->_createItem($label, $value));
			}
		}
		$collection->addFieldToFilter(
			'price',
			[
				'from' => $this->getMin(),
				'to' => $this->getMax()
			]
		);
		return $this;
	}
	public function getMax(){
		return $this->getCollectionWithoutFilter()->getMaxPrice();
	}
	public function getMin(){
		return $this->getCollectionWithoutFilter()->getMinPrice();
	}
	protected function getTo($from){
		$to = '';
		$interval = $this->dataProvider->getInterval();
		if ($interval && is_numeric($interval[1]) && $interval[1] > $from) {
			$to = $interval[1];
		}
		return $to;
	}
	protected function getFrom($from){
		$to = '';
		$interval = $this->dataProvider->getInterval();
		if ($interval && is_numeric($interval[0]) && $interval[0] < $from) {
			$to = $interval[0];
		}
		return $to;
	}
	protected function _getItemsData(){
		$values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		$attribute = $this->getAttributeModel();
		$productCollection = $this->getLayer()->getProductCollection();
		$facets = $this->getCollectionWithoutFilter()->getFacetedData($attribute->getAttributeCode());
		$data = [];
		if(!empty($facets)){
			$i=0;
			foreach ($facets as $key => $aggregation) {
				if (strpos($key, '_') === false) {
					continue;
				}
				list($from, $to) = explode('_', $key);
				if($from == '*') {
					$from = $this->getMin();
				}
				if($to == '*') {
					$to = $this->getMax();
				}
				$to -= self::PRICE_DELTA;
				// Improved price ranges
				if($from >= $to){
					if($i>0){
						if($from >= $data[$i-1]['to']){
							$merged = $data[$i-1];
							$merged['count'] += $aggregation['count'];
							$merged['to'] = $from;
							$merged['value'] = $merged['from'].'-'.$merged['to'];
							$merged['label'] = $this->_renderRangeLabel($merged['from'], $merged['to']);
							$data[$i-1] = $merged;
						}
					}
					continue;
				}
				$item = [
					'label' => $this->_renderRangeLabel($from, $to),
					'value' => $from.'-'.$to,
					'count' => $aggregation['count'],
					'from' => $from,
					'to' => $to
				];
				$data[$i] = $item;
				$i++;
			}
		}
		if(count($data) > 1) {
			foreach ($data as $item) {
				$this->itemDataBuilder->addItemData(
					$item['label'],
					$item['value'],
					$item['count']
				);
			}
		}
		return $this->itemDataBuilder->build();
	}
	protected function _renderRangeLabel($fromPrice, $toPrice){
		$fromPrice = empty($fromPrice) ? 0 : $fromPrice * $this->getCurrencyRate();
		$toPrice = empty($toPrice) ? $toPrice : $toPrice * $this->getCurrencyRate();
		$formattedFromPrice = $this->priceCurrency->format($fromPrice);
		if ($toPrice === '') {
			return __('%1 and above', $formattedFromPrice);
		} elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
			return $formattedFromPrice;
		} else {
			return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
		}
    }
	protected function getCollectionWithoutFilter(){
		if (!$this->emptyCollection) {
			$productCollection = $this->getLayer()->getProductCollection();
			$this->emptyCollection = $this->collectionProvider->getCollection(
				$this->getLayer()->getCurrentCategory()
			);
			$this->emptyCollection->updateSearchCriteriaBuilder();
			$this->getLayer()->prepareProductCollection($this->emptyCollection);
			foreach ($productCollection->getAddedFilters() as $field => $condition) {
				if ($this->getAttributeModel()->getAttributeCode() == $field) {
					continue;
				}
				$this->emptyCollection->addFieldToFilter($field, $condition);
			}
		}
		return $this->emptyCollection;
	}
}