<?php namespace Vocalogic;

use Illuminate\Support\ServiceProvider;

use Vocalogic\Html\HtmlBuilder;
use Vocalogic\Html\FormBuilder;

class VocalogicServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadViewsFrom(__DIR__ . '/../views', 'vocalogic');

		$this->mergeConfigFrom(
			__DIR__ . '/../config/vocalogic.php', 'vocalogic'
		);

		$this->publishes([
			__DIR__ . '/../config/vocalogic.php' => config_path('vocalogic.php'),
		], 'config');

		$this->publishes([
			__DIR__ . '/../views' => base_path('resources/views/vendor/vocalogic'),
		], 'views');

		$this->publishes([
			__DIR__ . '/../database/migrations/' => database_path('migrations'),
		], 'migrations');

		$this->publishes([
			__DIR__ . '/../public/js/' => public_path('js'),
		], 'public');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerHtmlBuilder();

		$this->registerFormBuilder();

		$this->app->alias('html', 'Vocalogic\Html\HtmlBuilder');
		$this->app->alias('form', 'Vocalogic\Html\FormBuilder');

		$this->app->bind(
			'Vocalogic\Html\FieldDecoratorInterface',
			'Vocalogic\Html\FieldDecorator'
		);
	}

	/**
	 * Register the HTML builder instance.
	 *
	 * @return void
	 */
	protected function registerHtmlBuilder()
	{
		$this->app->singleton('html', function($app)
		{
			return new HtmlBuilder($app['url'], $app['view']);
		});
	}

	/**
	 * Register the form builder instance.
	 *
	 * @return void
	 */
	protected function registerFormBuilder()
	{
		$this->app->singleton('form', function($app)
		{
			$form = new FormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->token());

			return $form->setSessionStore($app['session.store']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('html', 'form', 'Vocalogic\Html\HtmlBuilder', 'Vocalogic\Html\FormBuilder');
	}

}
