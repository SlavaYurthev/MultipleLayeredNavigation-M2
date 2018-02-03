<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\Layer;

class ItemCollectionProvider implements \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface {
	private $collectionFactory;
	public function __construct(
		\SY\MultipleLayeredNavigation\Model\ResourceModel\Fulltext\CollectionFactory $collectionFactory
	){
		$this->collectionFactory = $collectionFactory;
	}
	public function getCollection(\Magento\Catalog\Model\Category $category){
		if ($category->getParentId() == 1) {
			$collection = $this->collectionFactory->create(['searchRequestName' => 'quick_search_container']);
		} else {
			$collection = $this->collectionFactory->create();
			$collection->addCategoryFilter($category);
		}
		return $collection;
	}
}