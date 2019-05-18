<?php namespace Vocalogic;

use Auth;
use Psr\Log\LoggerInterface;

class CommandLog {

	public function __construct(LoggerInterface $log)
	{
		$this->log = $log;
	}

	public function handle($command, $next)
	{
		try
		{
			$user = Auth::user();
			$data = [
				'user_id'    => empty($user->id) ? null : $user->id,
				'command'    => get_class($command),
			];
			if (is_callable([$command, 'getAuditableParameters']))
			{
				$data['parameters'] = json_encode($command->getAuditableParameters());
			}
			elseif (is_callable([$command, 'getParameters']))
			{
				$data['parameters'] = json_encode($command->getParameters());
			}
			$audit = CommandAudit::create($data);
		}
		catch (Exception $e)
		{
			$this->log->error('CommandLog: failed to audit command ' . get_class($command));
		}

		$handle = $next($command);

		try
		{
			if (!empty($audit) && is_callable([$command, 'getAuditableResult']))
			{
				$audit->result = json_encode($command->getAuditableResult());
				$audit->save();
			}
			elseif (!empty($audit) && is_callable([$command, 'getResult']))
			{
				$audit->result = json_encode($command->getResult());
				$audit->save();
			}
		}
		catch (Exception $e)
		{
			$this->log->error('CommandLog: failed to get command result ' . get_class($command));
		}

		return $handle;
	}

}
