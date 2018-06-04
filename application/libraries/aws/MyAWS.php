<?php
define('AWS_SSL_CERTIFICATE', APPPATH.'libraries'.DIRECTORY_SEPARATOR.'aws'.DIRECTORY_SEPARATOR.'cacert.pem');
/**
* My custom AWS library
*/

// use Aws\Ses\SesClient;
// use Aws\Ses\Exception\SesException;
class Myaws
{
	var $charset = 'UTF-8';
	function __construct()
	{
		# code...
	}

	public function send_email($recipient, $subject, $message)
	{
		$client = Aws\Ses\SesClient::factory(array(
    		'credentials' => array(
			    'key' => getenv('AWS_KEY'),
			    'secret'  => getenv('AWS_SECRET'),
			  ),
    		'region' => 'us-east-1',
    		'version' => 'latest',
    		'http'    => [
		        'verify' => AWS_SSL_CERTIFICATE
		    ]
		));

		try {
		     $result = $client->sendEmail([
			    'Destination' => [
			        'ToAddresses' => [
			            $recipient,
			        ],
			    ],
			    'Message' => [
			        'Body' => [
			            'Html' => [
			                'Charset' => $this->charset,
			                'Data' => $message,
			            ],
						'Text' => [
			                'Charset' => $this->charset,
			                'Data' => strip_tags($message),
			            ],
			        ],
			        'Subject' => [
			            'Charset' => $this->charset,
			            'Data' => $subject,
			        ],
			    ],
			    'Source' => getenv('EMAIL_FROM'),
			    // If you are not using a configuration set, comment or delete the
			    // following line
			    //'ConfigurationSetName' => CONFIGSET,
			]);
		     $messageId = $result->get('MessageId');
		     return $messageId;

		} catch (Aws\Ses\Exception\SesException $error) {
			//$error->getAwsErrorMessage();
			return FALSE;
		}
	}

	private function init_s3()
	{
		$this->s3_client = new Aws\S3\S3Client([
		    'credentials' => array(
			    'key' => getenv('AWS_KEY'),
			    'secret'  => getenv('AWS_SECRET'),
			  ),
    		'region' => 'us-east-1',
    		'version' => 'latest',
    		'http'    => [
		        'verify' => AWS_SSL_CERTIFICATE
		    ]
		]);
	}

	public function get_s3_obj_url($bucket, $obj_name)
	{
		//Creating a presigned request
		if(!isset($this->s3_client))
			$this->init_s3();
		$cmd = $this->s3_client->getCommand('GetObject', [
		    'Bucket' => $bucket,
		    'Key'    => $obj_name
		]);

		$request = $this->s3_client->createPresignedRequest($cmd, '+60 minutes');
		$presignedUrl = (string) $request->getUri();
		return $presignedUrl;
	}

	public function delete_object($bucket, $obj_name)
	{
		//Creating a presigned request
		if(!isset($this->s3_client))
			$this->init_s3();
		$this->s3_client->deleteObject([
		    'Bucket' => $bucket,
		    'Key'    => $obj_name
		]);
	}

	public function delete_multi_objects($bucket, $objects)
	{
		//Creating a presigned request
		if(!isset($this->s3_client))
			$this->init_s3();
		$this->s3_client->deleteObjects([
		    'Bucket' => $bucket,
		    'Delete' => [
		        'Objects' => array_map(function ($key) {
		            return ['Key' => $key];
		        }, $objects)
		    ]
		]);
	}

	public function create_obj($bucket, $obj_name)
	{
		//Creating a presigned request
		if(!isset($this->s3_client))
			$this->init_s3();
		try {
		    // Upload data.
		    $result = $this->s3_client->putObject([
		        'Bucket' => $bucket,
		        'Key'    => $obj_name
		    ]);
		    return true;
		} catch (Aws\S3\Exception\S3Exception $e) {
		    return false;
		}
		return false;
	}

	public function create_obj_put_url($bucket, $obj_name, $mime)
	{
		//Creating a presigned request
		if(!isset($this->s3_client))
			$this->init_s3();
		$cmd = $this->s3_client->getCommand('PutObject', [
		    'Bucket' => $bucket,
		    'Key'    => $obj_name,
		    'ContentType' => $mime
		]);

		$request = $this->s3_client->createPresignedRequest($cmd, '+60 minutes');
		return (string) $request->getUri();
	}
}