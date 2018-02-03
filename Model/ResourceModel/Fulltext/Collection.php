<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\ResourceModel\Fulltext;
use Magento\Framework\App\ObjectManager;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection {
	protected $_addedFilters = [];
	protected $_decimalData = [];
	public function addFieldToFilter($field, $condition = null){
		if (is_string($field)) {
			$this->_addedFilters[$field] = $condition;
		}
		return parent::addFieldToFilter($field, $condition);
	}
	public function addCategoriesFilter(array $categoriesFilter){
		$this->addFieldToFilter('category_ids', $categoriesFilter);
		return $this;
	}
	public function getAddedFilters(){
		return $this->_addedFilters;
	}
	public function updateSearchCriteriaBuilder(){
		$searchCriteriaBuilder = ObjectManager::getInstance()
			->create(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
		$this->setSearchCriteriaBuilder($searchCriteriaBuilder);
		return $this;
	}
	protected function _prepareStatisticsData(){
		$this->_renderFilters();
		return parent::_prepareStatisticsData();
	}
}