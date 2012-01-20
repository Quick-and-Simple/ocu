~#comments the line
~#define('ROOT_VIEW',dirname(__FILE__) . '/examples-ts/'); 
~#$t = new TS('test',false,true);
~#$t->render();
.info
	%p
		=5
	-$a = 5;
	%font color="#{ $a==2 ? 'red' : 'blue'}
		=($a*5)
		
		- for ($i=0;$i<$a;$i++)
			="current iteration " . $i
			