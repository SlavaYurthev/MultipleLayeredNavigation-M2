<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Observer;
class Copyright implements \Magento\Framework\Event\ObserverInterface {
	public function execute(\Magento\Framework\Event\Observer $observer){
		$observer->getLayout()->addBlock(
			'Magento\Framework\View\Element\Text', 
			'sy_copyright_multiple_layered_navigation', 
			'sy_copyright'
		)->setData(
			'text',
			'<a href="https://slavayurthev.github.io/magento-2/extensions/multiple-layered-navigation/">Magento 2 Multiple Layered Navigation Extension</a>'
		);
		return $this;
	}
}