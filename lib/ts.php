<?php
	#examples:
	#	class Pseudo {
	#		var $error = 1;
	#		var $message = "absolute error\n";
	#		var $info = "full info text\n";		
	#		var $title = "undefined\n";
	#	}
	#	# the context can be accessed using $ctx-> inside the template
	#	$t = new TS('test',new Pseudo()); 
	#	# debug is by default false, so new TS('test',new Pseudo(),true) will enable debug
	#	# and the raw output the evaluated string at the beginning of the page wrapped around <!-- -->
	#	$t->render();
	#	# the renderer will autoload layout.ts in the same directory
	#	# so it becomes like this
	#	# layout.ts ([[YIELD]] is replaced with the view's generated output)
	#	# view.ts
	#
	#	test.ts:
	#	%html
	#		%head
	#			%title
	#				=$ctx->title
	#		%body
	#			.info
	#				%p
	#					=$ctx->info
	#				%font color="#{($ctx->error ? 'red' : 'blue')}
	#					=$ctx->message
	#	will produce this:
	#		<html >
	#		<head >
	#		<title >
	#		undefined
	#		</title>
	#		</head>
	#		<body >
	#		<div class='info' >
	#		<p >
	#		full info text
	#		</p>
	#		<font color="red >
	#		absolute error
	#		</font>
	#		</div>
	#		</body>
	#		</html>
	#		or this: (if PRETTY is enabled)
	#		<html >
	#			<head >
	#				<title >
	#					undefined
	#				</title>
	#			</head>
	#			<body >
	#				<div  class='info' >
	#					<p >
	#						full info text
	#					</p>
	#					<font color="red>
	#						absolute error
	#					</font>
	#				</div>
	#			</body>
	#		</html>	
	#############################
	#	example layout.ts
	#	%html
	#		%head
	#		
	#		%body
	#			[[YIELD]]
	#	
	#		.footer
	#			.copyright
	#				beer
	# tips and tricks
	# adding / at the end of % html block, will not auto close the block
	# so %br / , will output only <br />

	define('PRETTY',false);
	define('TYPE_CODE','-');
	define('TYPE_HTML','%');
	define('YIELD','[[YIELD]]');
	
	
	class NODE {
		var $nodes = array();
		var $type = TYPE_HTML;
		var $raw = '';
		var $rest = '';
		var $parent = false;
		var $indent = 0;
		var $data = '';
		var $lookup = array();
		var $word = false;
		function __construct($r='',$i=0) {
			$this->raw = $r;
			$this->indent = $i;
		}
		function add_node($n) {
			$n->parent = $this;
			array_push($this->nodes,$n);
		}
		function replace($d) {
			$d = preg_replace('/^\s+/','',$d);
			$d = preg_replace('/^=(.*)/','<?PHP printf("%s",(\\1));?>',$d);		
			$d = preg_replace('/#{(.*)?}/U','<?PHP printf("%s",(\\1));?>',$d);	
			return $d;	
		}
		function classify() {
			# simply parse
			# .info id='info-div' -> <div class='info' id='info-div'>
			# %td.info id='info-td' => <td class='info' id='info-td'>
			# - foreach ($ctx->list as $l)
			if (preg_match('/^\.\s?([A-Za-z0-9-_]+|".*?"|\'.*?\')\s?(.*)/',$this->raw,$m)) {
				$this->type = TYPE_HTML;
				$this->word = 'div';
				$quote = (preg_match('/[\'"]/',$m[1]) ? '' : '\'');
				$this->rest = " class=$quote{$m[1]}$quote ". $this->replace($m[2]);
			} else if (preg_match('/^%\s?([\w-]+)\.([A-Za-z0-9-_]+|".*?"|\'.*?\')(.*)/',$this->raw,$m)) {
				$this->type = TYPE_HTML;
				$this->word = $m[1];
				$quote = (preg_match('/[\'"]/',$m[2]) ? '' : '\'');				
				$this->rest = " class=$quote{$m[2]}$quote " . $this->replace($m[3]);
			} else if (preg_match('/^%\s?(\w+)(.*)/',$this->raw,$m)) {
				$this->type = TYPE_HTML;
				$this->word = $m[1];
				$this->rest = $this->replace($m[2]);
			} else if (preg_match('/^-\s?(\$?\w+)(.*)/',$this->raw,$m)) {
				$this->type = TYPE_CODE;
				$this->word = $m[1];
				$this->rest = $m[2];
			} else {
				throw new Exception("invalid string {$this->raw}");
			}
		}
		static function find_by_indent($node,$i) {
			while (($node = $node->parent)) {
				if ($node->indent == $i) {
					$node = $node->parent;
					break;
				}
			}
			return $node;
		}
		static function html_needs_closing($node) {
			return !preg_match('/\/\s?$/',$node->rest);
		}
		function add_data($d) {
			$d = $this->replace($d);
			$this->data .= $this->tabs(1). "$d\n";
		}
		function output() {
			return  $this->data;
		}
		function is_control() {
			return preg_match('/^(foreach|for|while|do|if|else|elsif|switch)$/',$this->word);
		}
		function tabs($more = 0) {
			if (!PRETTY)
				return '';
			return str_repeat("\t",$this->indent + $more);
		}

		function open() {
			if (!$this->word) 
				return '';
			
			if ($this->type == TYPE_CODE)
				return $this->tabs() . "<?PHP {$this->word} {$this->rest}".($this->is_control() ? ':' : '') . "?>\n";
			
			return $this->tabs() . "<{$this->word} {$this->rest}>\n";
		}
		function close() {
			if (!$this->word) 
				return '';
			
			if ($this->type == TYPE_CODE)
				if ($this->is_control())
					return $this->tabs() . "<?PHP end{$this->word};?>\n";
			if (NODE::html_needs_closing($this))
				return $this->tabs() . "</{$this->word}>\n";
			return '';
		}
		function walk($what) {
			$r = $what->open();
			$r .= $what->output();
			while ($w = array_shift($what->nodes)) {
				$r .= $w->walk($w);
			}
			$r .= $what->close();
			return $r;
		}
		function php($ctx,$debug = false) {
			ob_start();
			$r = $this->walk($this);
			eval('?>'.$r);
			return array($r,ob_get_clean());
		}
	}	
	class TS {
		var $context = false;
		var $debug = false;
		function __construct($file, $context = false,$debug = false,$layout_file_name = 'layout') {
			if (!$context || !is_object($context))
				throw new Exception('expecting object context');
			if (!defined('ROOT_VIEW'))
				throw new Exception('define ROOT_VIEW somewhere');
			# not since we will have to read it
			# lets do some name validation, we will allow only [a-zA-Z]
			if (!preg_match('/^[a-zA-Z_0-9]+$/',$file))
				throw new Exception("bad filename $file, accepting only [a-zA-Z_0-9]+");
			$subdir = DIRECTORY_SEPARATOR . strtolower(get_class($context)) . DIRECTORY_SEPARATOR;
			$file = ROOT_VIEW .$subdir . strtolower($file) . ".ts";
			
			
			#first look in the context's view folder for layout
			#if we dont find it, look inside root_view/
			$layout = ROOT_VIEW . $subdir . $layout_file_name . ".ts";
			if (!file_exists($layout))
				$layout = ROOT_VIEW  . $layout_file_name . ".ts";
			if (!file_exists($file))
				throw new Exception("$file does not exist");
			$this->layout = @preg_split("/(\n\r?|\r\n?)/",file_get_contents($layout));
			$this->view = preg_split("/(\n\r?|\r\n?)/",file_get_contents($file));
			$this->debug = $debug;
			$this->context = $context;
		}
		function _render($d,$prefix) {
			if (!$d)
				return YIELD;
			
			$indent = $prefix;
			$ROOT = new NODE();
			$ROOT->parent = $ROOT;
			$node = $ROOT;
			$lnum = 1;
			foreach ($d as $line) {
				$lnum++;
				if (preg_match('/(^(\s+)?~#)/',$line) || empty($line) || preg_match('/^\s+$/',$line))
					continue;
				if (preg_match('/^(\s+)?(%|-|\.)\s?.*/',$line,$m)) {
					$i = substr_count($m[1],"\t") + $prefix;
					$n = new NODE(preg_replace('/^\s+/','',$line),$i);
					$n->classify();
					if ($i - $indent > 1)
						throw new Exception("too much indentation at line $lnum");
					if ($i == $indent) {
						$node = $node->parent;
					} else if ($i < $indent){
						$node = NODE::find_by_indent($node,$i);
						if (!$node)
							throw new Exception("cant find indent $i at line $lnum");
					}
					$indent = $i;
					$node->add_node($n);
					$node = $n;
				} else {
					$plain_text_indent = $prefix;
					if (preg_match('/^(\s+)?/',$line,$m))
						$plain_text_indent = @substr_count($m[1],"\t") + $prefix;
					
					if ($plain_text_indent < $indent ) {
						throw new Exception("plain text indentation differ then block indent at line $lnum (plain: $plain_text_indent should be: ".($indent+1).") ($line)");
					}
					$node->add_data($line);
				}
			}
			return array($ROOT->php($this->context,$this->debug),$indent);
		}
		function render() {
			list(list($raw_layout,$l),$prefix) = $this->_render($this->layout,0);
			list(list($raw_view,$v),$prefix) = $this->_render($this->view,$prefix+1); #XXX: seems a bit hackish
			if ($this->debug) {
				echo "<!--\nLAYOUT:\n##################################################\n$raw_layout\nVIEW:\n##################################################\n$raw_view\n-->\n";
			}
			echo str_replace(YIELD,"\n$v",$l); #XXX: and this is also hackish
		}
	}
#	$t = new TS('test',false,true);
#	$t->render();
?>
