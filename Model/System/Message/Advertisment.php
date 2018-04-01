<?php
/**
 * Multiple Layered Navigation
 * 
 * @author Slava Yurthev
 */
namespace SY\MultipleLayeredNavigation\Model\System\Message;

class Advertisment implements \Magento\Framework\Notification\MessageInterface {
	protected $scopeConfig;
	protected $urlInterface;
	protected $configWriter;
	protected $cacheManager;
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Backend\Model\UrlInterface $urlInterface,
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
		\Magento\Framework\App\Cache\Manager $cacheManager
	){
		$this->scopeConfig = $scopeConfig;
		$this->urlInterface = $urlInterface;
		$this->configWriter = $configWriter;
		$this->cacheManager = $cacheManager;
	}
	public function getIdentity(){
		return 'sy_advertisment';
	}
	public function isDisplayed(){
		$time = $this->getConfig('time');
		if($time > 0){
			if($time < time()){
				return true;
			}
		}
		else{
			// initialization
			$this->configWriter->save(
				'sy_developer/advertisment/time',  
				strtotime('+3 days'), 
				\Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 
				0
			);
			$this->cacheManager->clean(['config']);
		}
		return false;
	}
	public function getText(){
		$html = "<p><strong>Hello</strong></p>";
		$html .= "<p>My name is <strong>Slava Yurthev</strong> and i am \"magento\" developer.</p>";
		$html .= "<p>You see this message because you use some extension developed by me, <strong>thanks for your choice</strong>.</p>";
		$html .= "<p>I want tell you about my hosting provider, this is really the best provider with lowest prices and highest quality.</p>";
		$html .= "<p>They are using \"SSD\" storage for speed up websites and this is work <strong>more than 60 times faster</strong> than \"HDD\".</p>";
		$html .= "<p>Do you want to <strong><a href=\"".$this->urlInterface->getUrl('sy_multiple_layered_navigation/advertisment/subscribe')."\" target=\"_blank\">check this for free</a></strong> or just <a href=\"".$this->urlInterface->getUrl('sy_multiple_layered_navigation/advertisment/close')."\">close</a> this message?</p>";
		$html .= "<p>* You will get a <strong>free week</strong> for the tests.</p>";
		$html .= "<p>* You will get a <strong>free month</strong> if you will transfer your project to them.</p>";
		$html .= "<p>And sorry if you already have known about this provider.</p>";
		$html .= "<p><strong>Have a nice day ;)</strong></p>";
		return $html;
	}
	public function getSeverity(){
		return self::SEVERITY_CRITICAL;
	}
	public function getConfig($key){
		return $this->scopeConfig->getValue(
			'sy_developer/advertisment/'.$key, 
			\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE 
		);
	}
}