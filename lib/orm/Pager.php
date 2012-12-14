<?php

class Pager {
        protected $sep            = '<span class="spacer">..</span>';
		public $itemSep        = " | ";
        public $lblNext        = 'Next';
        public $lblPrev        = 'Previous';

        public function __construct($linkBefore, $linkAfter, $page, $maxpage, 
                $size = false) 
        {
				if ($size==false)
					$size             = array('left'=>1, 'center'=>2, 'right'=>0); 
                $this->linkBefore = $linkBefore;
                $this->linkAfter  = $linkAfter;
                $this->page       = $page;
                $this->maxpage    = $maxpage;
                $this->size       = $size;

        }

        protected function link($link, $selected, $text, $page){
				if ($selected) 
					return '<span class="page selected" page="'.$page.'">'.$text.'</span> ';
				else 
	              return '<a href="'.$link.'" class="page'.($selected?' selected':'').'" page="'.$page.'">'.$text.'</a> ';
        }

        public function getPrevNextHtml() {
				$html = false;
				if ($this->maxpage>0) {
	                $halfsize         = floor($this->size['center']/2);
	                if ($this->page > 0)
	                        $html.= $this->link($this->linkBefore . ($this->page-1) . $this->linkAfter, false, $this->lblPrev,($this->page-1));
	

	                if ($this->page < $this->maxpage-1)  { 
						$html = ($html==false)?'': $html. $this->itemSep;
                        $html.= $this->link($this->linkBefore . ($this->page + 1) . $this->linkAfter, false, $this->lblNext,$this->page+1);
					}
				}
				return $html;
        }

        public function getNumbersHtml() {
				$html = false;
				if ($this->maxpage>0) {
	                $halfsize         = floor($this->size['center']/2);
	                // if ($this->page > 0)
	                //         $html.= $this->link($this->linkBefore . ($this->page-1) . $this->linkAfter, false, $this->lblPrev,($this->page-1));

	                $callback         = create_function('$n', 'return ($n<0)?0:(($n>='.$this->maxpage.')?'.($this->maxpage-1).':$n);');
	                $pages            = array_unique(array_map($callback, array_merge(
	                        range(0, $this->size['left']), 
	                        range($this->page - $halfsize, $this->page + $halfsize),
	                        range($this->maxpage - $this->size['right'], $this->maxpage-1)
	                        )));
	                sort($pages);

	                $prevpage         = -1;
	                foreach ($pages as $i) {
	                        if ($prevpage + 1 != $i) { 
								$html = ($html==false)?'': $html. $this->itemSep;
								$html.= $this->sep;
							}
							$html = ($html==false)?'': $html. $this->itemSep;
	                        $html .= $this->link($this->linkBefore . $i . $this->linkAfter, $i == $this->page, $i+1,$i);
	                        $prevpage = $i;
	                }

	                // if ($this->page < $this->maxpage-1) 
	                //         $html.= $this->link($this->linkBefore . ($this->page + 1) . $this->linkAfter, false, $this->lblNext,$this->page+1);
				}
				return $html;
        }

		
        public function getHtml() {
				$html = false;
				if ($this->maxpage>0) {
	                $halfsize         = floor($this->size['center']/2);
	                if ($this->page > 0)
	                        $html.= $this->link($this->linkBefore . ($this->page-1) . $this->linkAfter, false, $this->lblPrev,($this->page-1));

	                $callback         = create_function('$n', 'return ($n<0)?0:(($n>='.$this->maxpage.')?'.($this->maxpage-1).':$n);');
	                $pages            = array_unique(array_map($callback, array_merge(
	                        range(0, $this->size['left']), 
	                        range($this->page - $halfsize, $this->page + $halfsize),
	                        range($this->maxpage - $this->size['right'], $this->maxpage-1)
	                        )));
	                sort($pages);

	                $prevpage         = -1;
	                foreach ($pages as $i) {
	                        if ($prevpage + 1 != $i) $html.= $this->sep;
	                        $html .= $this->link($this->linkBefore . $i . $this->linkAfter, $i == $this->page, $i+1,$i);
	                        $prevpage = $i;
	                }

	                if ($this->page < $this->maxpage-1) 
	                        $html.= $this->link($this->linkBefore . ($this->page + 1) . $this->linkAfter, false, $this->lblNext,$this->page+1);
				}
				return $html;
        }
}
