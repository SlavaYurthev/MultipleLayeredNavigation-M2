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
	protected $moduleDir;
	protected $url = 'https://slavayurthev.github.io/advertisment.json';
	protected $file = 'etc/advertisment.json';
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
		$this->moduleDir = dirname(dirname(dirname(__DIR__)));
	}
	public function getIdentity(){
		return 'sy_advertisment';
	}
	public function isDisplayed(){
		$time = $this->getConfig('time');
		if($time > 0){
			if($time < time()){
				if(!file_exists(rtrim($this->moduleDir, '/').'/'.ltrim($this->file, '/'))){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $this->url);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
					curl_setopt($ch, CURLOPT_TIMEOUT, 15);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$json = curl_exec($ch);
					$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					curl_close($ch);
					if($httpCode >= 400){
						$this->configWriter->save(
							'sy_developer/advertisment/time',  
							strtotime('+30 days'), 
							\Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 
							0
						);
						$this->cacheManager->clean(['config']);
					}
					else{
						file_put_contents(rtrim($this->moduleDir, '/').'/'.ltrim($this->file, '/'), $json);
						return true;
					}
				}
				else{
					return true;
				}
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
		try {
			$advertisment = json_decode(file_get_contents(rtrim($this->moduleDir, '/').'/'.ltrim($this->file, '/')), true);
			if(isset($advertisment['message']['text'])){
				if(isset($advertisment['message']['variables'])){
					if(is_array($advertisment['message']['variables']) && !empty($advertisment['message']['variables'])){
						foreach ($advertisment['message']['variables'] as $key => $value) {
							$advertisment['message']['variables'][$key] = $this->urlInterface->getUrl('sy_multiple_layered_navigation/'.$value);
						}
						$advertisment['message']['text'] = __($advertisment['message']['text'], $advertisment['message']['variables']);
					}
				}
				return $advertisment['message']['text'];
			}
		} catch (\Exception $e) {}
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