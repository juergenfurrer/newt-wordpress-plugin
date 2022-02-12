<?php

namespace Infonique\Newt\Backend;

if (!defined('ABSPATH')) {
	die('');
}

class Main
{
	public function __construct()
	{
		add_action('wp_head', [$this, 'addHelloToHeader']);
	}

	public function addHelloToHeader()
	{
		echo 'Hello little Header';
	}

	public function getCreatorName()
	{
		return "infonique.ch";
	}
}
