<?php
if (!class_exists('bigml')) {
  include '../bigml/bigml.php';
}
class BigMLTestDownloadDataset extends PHPUnit_Framework_TestCase
{
    protected static $username; # "you_username"
    protected static $api_key; # "your_api_key"

    protected static $api;

    public static function setUpBeforeClass() {
       self::$api =  new BigML(self::$username, self::$api_key, true);
       ini_set('memory_limit', '512M');
       if (!file_exists('tmp')) {
          mkdir('tmp');
       }
    }
    /*
      Successfully exporting a dataset
    */

    public function test_scenario1() {
      $data = array(array('filename' => 'data/iris.csv', 'local_file' => 'tmp/exported_iris.csv' ));


      foreach($data as $item) {
          print "\nSuccessfully exporting a dataset\n";
          print "Given I create a data source uploading a ". $item["filename"]. " file\n";
          $source = self::$api->create_source($item["filename"], $options=array('name'=>'local_test_source'));
          $this->assertEquals(BigMLRequest::HTTP_CREATED, $source->code);
          $this->assertEquals(1, $source->object->status->code);

          print "And I wait until source is ready\n";
          $resource = self::$api->_check_resource($source->resource, null, 20000, 30);
          $this->assertEquals(BigMLRequest::FINISHED, $resource["status"]);

          print "And I create dataset with local source\n";
          $dataset = self::$api->create_dataset($source->resource);
          $this->assertEquals(BigMLRequest::HTTP_CREATED, $dataset->code);
          $this->assertEquals(BigMLRequest::QUEUED, $dataset->object->status->code);

          print "And I wait until dataset is ready " . $dataset->resource . " \n";
          $resource = self::$api->_check_resource($dataset->resource, null, 20000, 30);
          $this->assertEquals(BigMLRequest::FINISHED, $resource["status"]);

          print "And I download the dataset file to " . $item["local_file"] ." \n";
          $filename = self::$api->download_dataset($dataset->resource, $item["local_file"]);
          $this->assertNotNull($filename);

	  print "Then the download dataset file is like " . $item["filename"] . "\n";
	  $this->assertTrue(compareFiles($item["filename"], $item["local_file"]));
 
      } 
    }
}    