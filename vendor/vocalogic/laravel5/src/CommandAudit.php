<?php namespace Vocalogic;

use Vocalogic\Eloquent\Model;

class CommandAudit extends Model {

	protected $fillable = ['user_id', 'command', 'parameters', 'result'];

}
