<?php namespace Vocalogic\Html;

trait BootstrapSize {

	// lg (large), default (normal), sm (small) or xs (extra small)
	protected $size = 'default';

	/**
	 * Set the size to 'lg' (large)
	 *
	 * @return mixed
	 */
	public function lg()
	{
		$this->size = 'lg';
		return $this;
	}

	/**
	 * Set the size to 'default' (normal)
	 *
	 * @return mixed
	 */
	public function df()
	{
		$this->size = 'default';
		return $this;
	}

	/**
	 * Set the size to 'sm' (small)
	 *
	 * @return mixed
	 */
	public function sm()
	{
		$this->size = 'sm';
		return $this;
	}

	/**
	 * Set the size to 'xs' (extra small)
	 *
	 * @return mixed
	 */
	public function xs()
	{
		$this->size = 'xs';
		return $this;
	}

}
