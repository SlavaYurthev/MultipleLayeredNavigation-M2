<?php
/**
 * MultipleLayeredNavigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Controller\Index;

use Magento\Framework\App\Action\Action;

class Index extends Action {
	protected $resultPageFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	){
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
	}
	public function execute() {
		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set(__('Slava Yurthev Copyright'));
		$layout = $resultPage->getLayout();
		$layout->addBlock(
			'SY\MultipleLayeredNavigation\Block\Copyright', 
			'sy_copyright', 
			'content'
		);
		$this->_eventManager->dispatch('sy_copyright', ['layout' => $layout]);
		return $resultPage;
	}
}