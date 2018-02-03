<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\Layer\Filter;
use Magento\Framework\App\ObjectManager;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item {
	public function getRemoveUrl(){
		return $this->_url->getRemoveFilterUrl(
			$this->getFilter()->getRequestVar(),
			$this->getValue(),
			[$this->_htmlPagerBlock->getPageVarName() => null]
		);
	}
	public function getUrl(){
		return $this->_url->getFilterUrl(
			$this->getFilter()->getRequestVar(),
			$this->getValue(),
			[$this->_htmlPagerBlock->getPageVarName() => null],
			false
		);
	}
	public function isActive(){
		$values = ObjectManager::getInstance()->create(
				\SY\MultipleLayeredNavigation\Model\Url\Builder::class
			)
			->getValuesFromUrl($this->getFilter()->getRequestVar());
		if(!empty($values)){
			return in_array($this->getValue(), $values);
		}
	}
}