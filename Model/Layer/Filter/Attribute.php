<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\Layer\Filter;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute {
	protected $tagFilter;
	protected $urlBuilder;
	protected $collectionProvider;
	public function __construct(
		\Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Layer $layer,
		\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
		\Magento\Framework\Filter\StripTags $tagFilter,
		\SY\MultipleLayeredNavigation\Model\Url\Builder $urlBuilder,
		\SY\MultipleLayeredNavigation\Model\Layer\ItemCollectionProvider $collectionProvider,
		array $data = []
	){
		parent::__construct(
			$filterItemFactory,
			$storeManager,
			$layer,
			$itemDataBuilder,
			$tagFilter,
			$data
		);
		$this->tagFilter = $tagFilter;
		$this->urlBuilder = $urlBuilder;
		$this->collectionProvider = $collectionProvider;
	}
	public function apply(\Magento\Framework\App\RequestInterface $request){
		$values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		if (!$values){
			return $this;
		}
		$productCollection = $this->getLayer()->getProductCollection();
		$this->applyToCollection($productCollection);
		foreach ($values as $value){
			$label = $this->getOptionText($value);
			$this->getLayer()->getState()->addFilter($this->_createItem($label, $value));
		}
		return $this;
	}
	public function applyToCollection($collection){
		$attribute = $this->getAttributeModel();
		$attributeValue = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		if (empty($attributeValue)){
			return $this;
		}
		$collection->addFieldToFilter($attribute->getAttributeCode(), array('in' => $attributeValue));
	}
	protected function _getItemsData(){
		$values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
		$productCollection = $this->getLayer()->getProductCollection();
		$collection = $this->collectionProvider->getCollection($this->getLayer()->getCurrentCategory());
		$collection->updateSearchCriteriaBuilder();
		$this->getLayer()->prepareProductCollection($collection);
		foreach ($productCollection->getAddedFilters() as $field => $condition) {
			if ($this->getAttributeModel()->getAttributeCode() == $field) {
				continue;
			}
			$collection->addFieldToFilter($field, $condition);
		}
		$attribute = $this->getAttributeModel();
		$optionsFacetedData = $this->getFacetedData();
		$options = $attribute->getFrontend()->getSelectOptions();
		foreach ($options as $option) {
			if(empty($option['value'])) {
				continue;
			}
			if(isset($optionsFacetedData[$option['value']])){
				$count = $this->getOptionItemsCount($optionsFacetedData, $option['value']);
				$this->itemDataBuilder->addItemData(
					$this->tagFilter->filter($option['label']),
					$option['value'],
					$count
				);
			}
		}
		return $this->itemDataBuilder->build();
	}
	private function getOptionItemsCount($faceted, $key){
		if(isset($faceted[$key]['count'])){
			return $faceted[$key]['count'];
		}
		return 0;
	}
	private function getFacetedData(){
		$collection = $this->collectionProvider->getCollection($this->getLayer()->getCurrentCategory());
		$collection->updateSearchCriteriaBuilder();
		$collection->addCategoryFilter($this->getLayer()->getCurrentCategory());
		return $collection->getFacetedData($this->getAttributeModel()->getAttributeCode());
	}
}