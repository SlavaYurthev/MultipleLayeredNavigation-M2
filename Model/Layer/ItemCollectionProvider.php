<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\Layer;

class ItemCollectionProvider implements \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface {
	private $storeManager;
	private $collectionFactory;
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\SY\MultipleLayeredNavigation\Model\ResourceModel\Fulltext\CollectionFactory $collectionFactory
	){
		$this->storeManager = $storeManager;
		$this->collectionFactory = $collectionFactory;
	}
	public function getCollection(\Magento\Catalog\Model\Category $category){
		if ($category->getId() == $this->storeManager->getStore()->getRootCategoryId()) {
			$collection = $this->collectionFactory->create(['searchRequestName' => 'quick_search_container']);
		} else {
			$collection = $this->collectionFactory->create();
			$collection->addCategoryFilter($category);
		}
		return $collection;
	}
}