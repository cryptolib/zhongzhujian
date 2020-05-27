<?php

namespace Web\Controller;

class Index extends Base
{
	public function index()
	{
		$this->redirect('/balance/');
		$this->render('index.html');
	}
}