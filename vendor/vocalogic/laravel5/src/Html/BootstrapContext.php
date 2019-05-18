<?php namespace Vocalogic\Html;

trait BootstrapContext {

	// default, primary, success, info, warning, danger or link
	protected $context = 'default';

	/**
	 * Set the context to 'default'
	 *
	 * @return mixed
	 */
	public function defaultContext()
	{
		$this->context = 'default';
		return $this;
	}

	/**
	 * Set the context to 'primary'
	 *
	 * @return mixed
	 */
	public function primary()
	{
		$this->context = 'primary';
		return $this;
	}

	/**
	 * Set the context to 'success'
	 *
	 * @return mixed
	 */
	public function success()
	{
		$this->context = 'success';
		return $this;
	}

	/**
	 * Set the context to 'info'
	 *
	 * @return mixed
	 */
	public function info()
	{
		$this->context = 'info';
		return $this;
	}

	/**
	 * Set the context to 'warning'
	 *
	 * @return mixed
	 */
	public function warning()
	{
		$this->context = 'warning';
		return $this;
	}

	/**
	 * Set the context to 'danger'
	 *
	 * @return mixed
	 */
	public function danger()
	{
		$this->context = 'danger';
		return $this;
	}

	/**
	 * Set the context to 'link'
	 *
	 * @return mixed
	 */
	public function link()
	{
		$this->context = 'link';
		return $this;
	}

}
