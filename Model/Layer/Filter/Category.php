<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\Layer\Filter;
use Magento\Framework\App\ObjectManager;

class Category extends \Magento\CatalogSearch\Model\Layer\Filter\Category {
	protected $escaper;
	protected $dataProvider;
	protected $urlBuilder;
	protected $collectionProvider;
	public function __construct(
		\Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Layer $layer,
		\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
		\Magento\Framework\Escaper $escaper,
		\Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
		\SY\MultipleLayeredNavigation\Model\Url\Builder $urlBuilder,
		\SY\MultipleLayeredNavigation\Model\Layer\ItemCollectionProvider $collectionProvider,
		array $data = []
	){
		parent::__construct(
			$filterItemFactory,
			$storeManager,
			$layer,
			$itemDataBuilder,
			$escaper,
			$categoryDataProviderFactory,
			$data
		);
		$this->escaper = $escaper;
		$this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
		$this->urlBuilder = $urlBuilder;
		$this->collectionProvider = $collectionProvider;
	}
	public function apply(\Magento\Framework\App\RequestInterface $request){
		$values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		if (!$values) {
			return $this;
		}
		$productCollection = $this->getLayer()->getProductCollection();
		$this->applyToCollection($productCollection);
		$categoryCollection = ObjectManager::getInstance()->create(
			\Magento\Catalog\Model\ResourceModel\Category\Collection::class
		);
		$categoryCollection->addAttributeToFilter('entity_id', ['in' => $values])->addAttributeToSelect('name');
		$categoryItems = $categoryCollection->getItems();
		foreach ($values as $value) {
			if (isset($categoryItems[$value])) {
				$category = $categoryItems[$value];
				$label = $category->getName();
				$this->getLayer()
					->getState()
					->addFilter($this->_createItem($label, $value));
			}
		}
		return $this;
	}
	protected function _getItemsData(){
		$values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		$productCollection = $this->getLayer()->getProductCollection();
		$collection = $this->collectionProvider->getCollection($this->getLayer()->getCurrentCategory());
		$collection->updateSearchCriteriaBuilder();
		$this->getLayer()->prepareProductCollection($collection);
		foreach ($productCollection->getAddedFilters() as $field => $condition) {
			if ($field === 'category_ids') {
				$collection->addFieldToFilter($field, $this->getLayer()->getCurrentCategory()->getId());
				continue;
			}
			$collection->addFieldToFilter($field, $condition);
		}
		$optionsFacetedData = $collection->getFacetedData('category');
		$category = $this->dataProvider->getCategory();
		$categories = $category->getChildrenCategories();
		if ($category->getIsActive()) {
			foreach ($categories as $category) {
				if ($category->getIsActive()) {
					if(isset($optionsFacetedData[$category->getId()])){
						$count = $this->getOptionItemsCount($optionsFacetedData, $category->getId());
						$this->itemDataBuilder->addItemData(
							$this->escaper->escapeHtml($category->getName()),
							$category->getId(),
							$count
						);
					}
				}
			}
		}
		return $this->itemDataBuilder->build();
	}
	public function applyToCollection($collection){
		$values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		if (empty($values)) {
			return $this;
		}
		$collection->addCategoriesFilter(['in' => $values]);
		return $this;
	}
	private function getOptionItemsCount($faceted, $key){
		if(isset($faceted[$key]['count'])){
			return $faceted[$key]['count'];
		}
		return 0;
	}
}