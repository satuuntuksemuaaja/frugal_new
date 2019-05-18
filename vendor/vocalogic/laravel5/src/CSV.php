<?php namespace Vocalogic;

use League\Csv\Writer;
use SplTempFileObject;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CSV extends Response {

	protected $csv;
	protected $output;

	public static function newFile(array $items = [], array $headers = [])
	{
		$writer = Writer::createFromFileObject(new SplTempFileObject);

		if (!empty($headers))
		{
			$writer->insertOne($headers);
		}

		$writer->insertAll($items);

		return $writer;
	}

	public static function response($csv = null, $name = null, $headers = [], $contentDisposition = 'attachment', $status = 200)
	{
		return new static($csv, $name, $headers, $contentDisposition, $status);
	}

	public function __construct($csv, $name = null, $headers = [], $contentDisposition = 'attachment', $status = 200)
	{
		parent::__construct(null, $status, $headers);

		$this->setCsv($csv);

		if (is_null($name))
		{
			$this->setContentDisposition($contentDisposition);
		}
		else
		{
			$this->setContentDisposition($contentDisposition, $name, str_replace('%', '', Str::ascii($name)));
		}
	}

	public function setCsv($csv)
	{
		$this->csv = $csv;
	}

	public function getCsv()
	{
		return $this->csv;
	}

	public function setContentDisposition($disposition, $filename = '', $filenameFallback = '')
	{
		$dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
		$this->headers->set('Content-Disposition', $dispositionHeader);
		return $this;
	}

	public function prepare(Request $request)
	{
		$this->output = (string) $this->csv;

		$this->headers->set('Content-Length', strlen($this->output));

		if (!$this->headers->has('Content-Type')) {
			$this->headers->set('Content-Type', 'text/csv; charset=UTF-8');
		}

		if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
			$this->setProtocolVersion('1.1');
		}

		$this->ensureIEOverSSLCompatibility($request);

		return $this;
	}

	public function sendContent()
	{
		if (!$this->isSuccessful()) {
			parent::sendContent();

			return;
		}

		$out = fopen('php://output', 'wb');

		fwrite($out, $this->output);

		fclose($out);
	}

	public function setContent($content)
	{
		if (null !== $content) {
			throw new \LogicException('The content cannot be set on a BinaryFileResponse instance.');
		}
	}

	public function getContent()
	{
		return false;
	}

}
