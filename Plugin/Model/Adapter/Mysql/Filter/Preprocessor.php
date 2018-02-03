<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Plugin\Model\Adapter\Mysql\Filter;

class Preprocessor {
	protected $urlBuilder;
	public function __construct(
		\SY\MultipleLayeredNavigation\Model\Url\Builder $urlBuilder
	){
		$this->urlBuilder = $urlBuilder;
	}
	public function aroundProcess(
		\Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject,
		\Closure $proceed,
		\Magento\Framework\Search\Request\FilterInterface $filter,
		$isNegation,
		$query
	){
		if($filter->getField() === 'price'){
			$values = $this->urlBuilder->getValuesFromUrl('price');
			if(!empty($values)){
				$statements = [];
				foreach ($values as $value) {
					list($from, $to) = explode("-", $value);
					$statement = [
						$this->getSqlStringByArray(
							[floatval($from)],
							'price',
							'>='
						),
						$this->getSqlStringByArray(
							[floatval($to)],
							'price',
							'<='
						)
					];
					$statements[] = '('.implode(" AND ", $statement).')';
				}
				return implode(" OR ", $statements);
			}
		}
		if($filter->getField() === 'category_ids'){
			if(is_array($filter->getValue())){
				if(isset($filter->getValue()['in'])){
					return $this->getSqlStringByArray($filter->getValue()['in']);
				}
				return $this->getSqlStringByArray($filter->getValue());
			}
			elseif(is_string($filter->getValue())){
				return $this->getSqlStringByArray([$filter->getValue()]);
			}
		}
		return $proceed($filter, $isNegation, $query);
	}
	private function getSqlStringByArray(
		$array = [],
		$field = 'category_ids_index.category_id',
		$operator = '=',
		$rule = 'OR'
	){
		$statements = [];
		if(!empty($array)){
			foreach ($array as $value) {
				$statements[] = $field.' '.$operator.' '.$value;
			}
		}
		return implode(' '.$rule.' ', $statements);
	}
}