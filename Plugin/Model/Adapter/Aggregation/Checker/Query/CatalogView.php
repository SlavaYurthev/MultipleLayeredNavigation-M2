<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Plugin\Model\Adapter\Aggregation\Checker\Query;

use Magento\Framework\Search\RequestInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Exception\NoSuchEntityException;

class CatalogView {
	private $categoryRepository;
	private $storeManager;
	public function __construct(
		CategoryRepositoryInterface $categoryRepository,
		StoreManagerInterface $storeManager
	) {
		$this->categoryRepository = $categoryRepository;
		$this->storeManager = $storeManager;
	}
	public function aroundIsApplicable(
		\Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView $subject,
		\Closure $proceed,
		RequestInterface $request
	){
		if ($request->getName() === 'catalog_view_container') {
			return $this->hasAnchorCategory($request);
		}
		return $proceed($request);
	}
	private function hasAnchorCategory(RequestInterface $request){
		$queryType = $request->getQuery()->getType();
		$result = false;
		if ($queryType === QueryInterface::TYPE_BOOL) {
			$categories = $this->getCategoriesFromQuery($request->getQuery());
			foreach ($categories as $category) {
				if ($category && $category->getIsAnchor()) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}
	private function getCategoriesFromQuery(QueryInterface $queryExpression){
		$categoryIds = $this->getCategoryIdsFromQuery($queryExpression);
		$categories = [];
		foreach ($categoryIds as $categoryId) {
			try {
				$categories[] = $this->categoryRepository
					->get($categoryId, $this->storeManager->getStore()->getId());
			} catch (NoSuchEntityException $e) {}
		}
		return $categories;
	}
	private function getCategoryIdsFromQuery(QueryInterface $queryExpression){
		$queryFilterArray = [];
		$queryFilterArray[] = $queryExpression->getMust();
		$queryFilterArray[] = $queryExpression->getShould();
		$categoryIds = [];
		foreach ($queryFilterArray as $item) {
			if (!empty($item) && isset($item['category'])) {
				$queryFilter = $item['category'];
				$values = $queryFilter->getReference()->getValue();
				if (is_array($values)) {
					$categoryIds = array_merge($categoryIds, $values['in']);
				} else {
					$categoryIds[] = $values;
				}
			}
		}
		return $categoryIds;
	}
}