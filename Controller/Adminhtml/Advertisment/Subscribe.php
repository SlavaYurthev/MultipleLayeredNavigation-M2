<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Controller\Adminhtml\Advertisment;

class Subscribe extends \Magento\Backend\App\Action {
	protected $resultRedirectFactory;
	public function __construct(
		\Magento\Backend\App\Action\Context $context, 
		\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
	){
		parent::__construct($context);
		$this->resultRedirectFactory = $resultRedirectFactory;
	}
	public function execute(){
		$this->_objectManager->get('Magento\Framework\App\Config\Storage\WriterInterface')->save(
			'sy_developer/advertisment/subscribed',  
			1, 
			\Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 
			0
		);
		$this->_objectManager->get('Magento\Framework\App\Cache\Manager')->clean(['config']);
		$resultRedirectFactory = $this->resultRedirectFactory->create();
		$resultRedirectFactory->setUrl('https://accounts.fozzy.com/aff.php?aff=1132');
		return $resultRedirectFactory;
	}
	protected function _isAllowed(){
		return true;
	}
}