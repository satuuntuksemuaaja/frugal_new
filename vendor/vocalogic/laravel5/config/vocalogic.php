<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Form Builder - default form, label and field attributes
	|--------------------------------------------------------------------------
	*/

	'formAttributes'  => ['novalidate' => true],
	'labelAttributes' => ['class' => 'col-md-4 control-label'],
	'fieldAttributes' => ['class' => 'form-control', '_template' => 'vocalogic::field-decorator', '_span' => '8'],

	/*
	|--------------------------------------------------------------------------
	| Exception Handler - include stack trace at VocalogicException log?
	|--------------------------------------------------------------------------
	*/
	'logVocalogicExceptionStackTrace' => false,

];
