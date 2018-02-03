<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\Url;

class Builder extends \Magento\Framework\Url {
	public function getFilterUrl($code, $value, $query = [], $singleValue = false){
		$params = ['_current' => true, '_use_rewrite' => true, '_query' => $query];
		$values = array_unique(
			array_merge(
				$this->getValuesFromUrl($code), 
				[$value]
			)
		);
		$params['_query'][$code] = implode(',', $values);
		return urldecode($this->getUrl('*/*/*', $params));
	}
	public function getRemoveFilterUrl($code, $value, $query = []){
		$params = ['_current' => true, '_use_rewrite' => true, '_query' => $query, '_escape' => true];
		$values = $this->getValuesFromUrl($code);
		$key = array_search($value, $values);
		unset($values[$key]);
		$params['_query'][$code] = $values ? implode(',', $values) : null;
		return urldecode($this->getUrl('*/*/*', $params));
	}
	public function getValuesFromUrl($code){
		return array_filter(explode(',', $this->_getRequest()->getParam($code)));
	}
}