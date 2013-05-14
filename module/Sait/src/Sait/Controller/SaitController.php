<?php
/**
 * @author Rustam Ibragimov
 * @mail Rustam.Ibragimov@softline.ru
 * @date 07.05.13
 */
namespace Sait\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\AcceptableViewModelSelector;
use Zend\View\Model\ViewModel;
use Sait\Model\Sait;
use Sait\Form\SaitForm;
use Sait\Form\ParserForm;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class SaitController extends AbstractActionController
{
	protected $SaitTable;

	public function indexAction()
	{
		$categorys = $this->getSaitTable()->fetchAll('category');
		return new viewModel(array(
			'categorys' => $categorys,
		));
	}

	public function getSaitTable()
	{
		if (!$this->SaitTable) {
			$sm = $this->getServiceLocator();
			$this->SaitTable = $sm->get('Sait\Model\SaitTable');
		}
		return $this->SaitTable;
	}

	public function addAction()
	{
		$form = new SaitForm();
		$form->get('submit')->setValue('Add');

		$request = $this->getRequest();
		if ($request->isPost()) {
			$sait = new Sait();
			$form->setInputFilter($sait->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$sait->exchangeArray($form->getData());
				$saitArray = array(
					'title' => $sait->title,
					'url' => $sait->url,
				);
				$this->getSaitTable()->addOne('category', $saitArray);
				return $this->redirect()->toUrl('/');
			}
		}
		return array('form' => $form);
	}

	public function parsecatAction()
	{
		$logger = new Logger();
		$writer = new Stream($_SERVER['DOCUMENT_ROOT'] . '/../logs/newproject.log');
		$logger->addWriter($writer);
		$sStartTime = time();
		$request = $this->getRequest();

		if ($request->isPost()) {
			if($request->getPost()->submit == "Парсить") {
				echo 1;die;
				$id = (int) $this->params()->fromRoute('id');
				$category = $this->getSaitTable()->getAllByVal('category',array('id' => $id));
				foreach($category as $cat) {
					$sUrl = $cat['url'];
					$sTitle = $cat['title'];
				}

				if($sCategoryContent = file_get_contents($sUrl)) {
					$logger->log(Logger::INFO, 'Парсинг категории (1 стр.) прошел успешно');
				} else {
					$logger->log(Logger::INFO, 'Парсинг категории (1 стр.) провалился');
					return new viewModel(array(
						'msg' => '<br>Парсинг категории (1 стр.) провалился<br>',
						'sStartTime' => $sStartTime,
					));
				};
				preg_match_all('/(?<=b-offers__name" href="\/model\.xml\?modelid\=)(.*?)(?=&amp;hid)/',$sCategoryContent,$aUrls);
				preg_match_all('/(?<=моделей).*?(\d+)(?=<)/',$sCategoryContent,$aCounts);
				$iCount = (int)$aCounts[1][0];
				if($iCount%10 == 0)
				{
					$iCount =  $iCount-10;
				} else {
					$iCount = (floor($iCount/10)+1)*10-10;
				}

				$aAllUrls[] = $aUrls[0];
				for($i=10;$i<=10;$i+=10)
				{
					sleep(2);
					$sUrl = str_replace('BPOS=0','BPOS=' . $i,$sUrl);
					if($sCategoryContent = file_get_contents($sUrl)) {
						$logger->log(Logger::INFO, "Парсинг категории ($i/10 стр.) прошел успешно");
						echo "<br>Парсинг категории ($i/10 стр.) прошел успешно<br>";
					} else {
						$logger->log(Logger::INFO, "Парсинг категории ($i/10 стр.) провалился");
						return new viewModel(array(
							'msg' => "<br>Парсинг категории ($i/10 стр.) провалился<br>",
							'sStartTime' => $sStartTime,
						));
					};
					preg_match_all('/(?<=b-offers__name" href="\/model\.xml\?modelid\=)(.*?)(?=&amp;hid)/',$sCategoryContent,$aUrls);
					$aAllIds[] = $aUrls[0];
				}

				foreach($aAllIds as $aAllId)
				{
					foreach($aAllId as $sId)
					{
						sleep(2);
						$sUrl = 'http://market.yandex.ru/model.xml?modelid=' . $sId;
						if($sCategoryContent = file_get_contents($sUrl)) {
							$logger->log(Logger::INFO, "Парсинг продукта (id = $sId) прошел успешно");
							echo "Парсинг продукта (id = $sId) прошел успешно<br>";
						} else {
							$logger->log(Logger::INFO, "Парсинг продукта (id = $sId) провалился");
							return new viewModel(array(
								'msg' => "Парсинг продукта (id = $sId) провалился<br>",
								'sStartTime' => $sStartTime,
							));
						};
						preg_match('/(?<=href="http:\/\/mdata\.yandex\.net\/i\?path=)(.*?)(?=")/',$sCategoryContent,$aUrls);
						preg_match('/(?<=b-page-title_type_model">)(.*?)(?=<)/',$sCategoryContent,$aTitles);
						$sProductTitle = $aTitles[0];
						$sImgUrl = 'http://mdata.yandex.net/i?path=' . $aUrls[0];
						if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/products/' . $aUrls[0]))
						{
							if(file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/img/products/' . $aUrls[0], file_get_contents($sImgUrl))) {
								$logger->log(Logger::INFO, "Парсинг картинки продукта (id = $sId) прошел успешно");
								echo "Парсинг картинки продукта (id = $sId) прошел успешно<br>";
							} else {
								$logger->log(Logger::INFO, "Парсинг картинки продукта (id = $sId) провалился");
								return new viewModel(array(
									'msg' => "Парсинг картинки продукта (id = $sId) провалился<br>",
									'sStartTime' => $sStartTime,
								));
							};
						}
						$aProducts[] = array(
							'ya_id' => $sId,
							'title' => $sProductTitle,
							'img_name' => $aUrls[0],
							'category_id' => $id,
						);
					}
				}

				foreach($aProducts as $aProduct)
				{
					if($this->getSaitTable()->addOne('product',  $aProduct))
					{
						$logger->log(Logger::INFO, "Запись в БД (id = {$aProduct['ya_id']}) провалился");
						echo 'ok<br>';
					};
				}

				return new viewModel(array(
					'url' => $sUrl,
					'title' => $sTitle,
					'sStartTime' => $sStartTime,
				));
			}
		} else {
			$id = (int) $this->params()->fromRoute('id');
			$oProducts = $this->getSaitTable()->getAllByVal('product',array('category_id' => $id));
			foreach($oProducts as $aProduct) {
				$sId = $aProduct['id'];
				$sYaId = $aProduct['ya_id'];
				$sImgName = $aProduct['img_name'];
				$sTitle = $aProduct['title'];
			}
			if(isset($sTitle)) {
				$logger->log(Logger::INFO, 'Данные из БД получены');
				$aProduct = array(
					'ya_id' => $sYaId,
					'id'    => $sId,
					'img_name' => $sImgName,
					'title' => $sTitle,
					'category_id' => $id,
				);
				return new viewModel(array(
					'aProduct' => $aProduct,
					'sStartTime' => $sStartTime,
				));
			} else {
				$form = new ParserForm();
				$form->get('submit')->setValue('Парсить');
				$logger->log(Logger::INFO, 'Данные из БД не получены');
				return new viewModel(array(
					'msg' => 'Данные из БД не получены<br>',
					'form' => $form,
				));
			}
		}
	}
}