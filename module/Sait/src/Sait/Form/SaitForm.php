<?php
/**
 * @author Rustam Ibragimov
 * @mail Rustam.Ibragimov@softline.ru
 * @date 07.05.13
 */
namespace Sait\Form;

use Zend\Form\Form;

class SaitForm extends Form
{
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct('sait');
		$this->setAttribute('method', 'post');
		$this->add(array(
			'name' => 'id',
			'attributes' => array(
				'type'  => 'hidden',
			),
		));
		$this->add(array(
			'name' => 'title',
			'attributes' => array(
				'type'  => 'text',
			),
			'options' => array(
				'label' => 'Title',
			),
		));
		$this->add(array(
			'name' => 'url',
			'attributes' => array(
				'type'  => 'text',
			),
			'options' => array(
				'label' => 'Url',
			),
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Go',
				'id' => 'submitbutton',
			),
		));
	}
}