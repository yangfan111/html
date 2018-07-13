<?php
class Test{
	static function t1($a,$b)
	{
		echo 'summm';
	}
	
}
class Test2{
	public function init ()
	{
		$t1f = Test::t1;
		$t1f(1,2);
	}	
}
$t5 = new Test2();
$t5->init();