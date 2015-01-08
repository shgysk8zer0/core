<?php
namespace shgysk8zer0\Core\Tests\Resources;
/**
 * Horrray for comments!!
 */
class Test extends \shgysk8zer0\Core\Abstracts\Unit_Test
{
	public $string = 'Hello World!';
	public $empty_string = '';
	public $int = 1;
	public $float = 3.14;
	public $numeric = '2.5818';
	public $zero = 0;
	public $empty_array = [];
	public $array = [0, 1];
	public $object;
	public $true = true;
	public $false = false;

	public function __construct()
	{
		$this->object = new \stdClass();
		parent::__construct('\\shgysk8zer0\\Core\\RegExp', ['Hello %NAME%']);
	}

	public function myMethod()
	{
		assert($this->int === 1, 'One equals one');
		assert(is_int($this->int), 'Is int');
		assert(is_float($this->float), 'Float test');
		assert(is_numeric($this->numeric), 'Numeric test');
		assert(is_array($this->array), 'Array asssert on array');
		assert(!isset($this->dne), '!isset test');
		assert(empty($this->empty_array), 'Array is empty');
		assert(!empty($this->array), 'Array is not empty');
		assert(is_object($this->object), 'Object is object');
		assert(is_bool($this->true), 'True is bool');
		assert($this->true, 'True is true');
		assert(!$this->false, 'False is false');
		assert(get_class() === __CLASS__, 'Same class');
		assert(
			get_class($this->set_pattern('%TEST%')) === get_class($this->reflected_class),
			'Chainable test'
		);
		assert(false, 'This assert should fail');
		assert('5 > 6', '5 > 6');

		echo 'Done!' . PHP_EOL;
		//print_r($this->getCaller());
		/*$this->assertEquals(1, 1, 'One equals one');
		$this->assertString('', 'String Test');
		$this->assertInt(1, 'Int Test');
		$this->assertFloat(3.14, 'Float Test');
		$this->assertNumeric('2.1858', 'Numeric Test');
		$this->assertBool(true, 'Bool test');
		$this->assertTrue(true, 'True test');
		$this->assertFalse(false, 'False test');
		$this->assertArray([], 'Array test');
		$this->assertObject(new \stdClass, 'Object Test');
		$this->assertEmpty([], 'Empty test');
		$this->assertNotEmpty('Hello World', 'NotEmpty Test');
		$this->assertClass(
			$this->set_pattern('title'),
			$this->getName(),
			'Chain returns class'
		);*/
		//print_r(['included files' => get_included_files()]);
	}
}
