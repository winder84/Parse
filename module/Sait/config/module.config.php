<?php
/**
 * @author Rustam Ibragimov
 * @mail Rustam.Ibragimov@softline.ru
 * @date 07.05.13
 */
return array(
	'controllers' => array(
		'invokables' => array(
			'Sait\Controller\Sait' => 'Sait\Controller\SaitController',
		),
	),
	'router' => array(
		'routes' => array(
			'sait' => array(
				'type'    => 'segment',
				'options' => array(
					'route'    => '[/:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id'     => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Sait\Controller\Sait',
						'action'     => 'index',
					),
				),
			),
		),
	),
	'view_manager' => array(
		'template_path_stack' => array(
			'sait' => __DIR__ . '/../view',
		),
	),
);