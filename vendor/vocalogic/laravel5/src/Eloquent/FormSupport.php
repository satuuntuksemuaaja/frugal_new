<?php namespace Vocalogic\Eloquent;

use Vocalogic\Html\Form;

trait FormSupport {

	protected $form = null;

	public function getFormAttribute()
	{
		if (is_null($form))
		{
			$form = new Form;
			$form->fields($this->getFields());
		}
		if (is_callable([$this, 'prepareForm']))
		{
			$this->prepareForm($this->form);
		}
		return $this->form;
	}

	public function render($options)
	{
		return $this->form->render($options);
	}

	public function getFields()
	{

	}

}
