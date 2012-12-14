<? 
class NextPrevPager {
	
    protected $sep            = ' | ';
    protected $lblNext        = 'Next »';
    protected $lblPrev        = '« Prev';

    public function __construct($linkBefore, $linkAfter, $page, $maxpage, $lblNext = false, $lblPrev = false) 
    {
            $this->linkBefore = $linkBefore;
            $this->linkAfter  = $linkAfter;
            $this->page       = $page;
            $this->maxpage    = $maxpage;
			if ($lblNext)
				$this->lblNext = $lblNext;
			if ($lblPrev)
				$this->lblPrev = $lblPrev;
    }

    protected function link($link, $selected, $text){
          return '<a href="'.$link.'" class="page'.($selected?' selected':'').'">'.$text.'</a> ';
    }

    public function getHtml() {
			$html = false;
			if ($this->maxpage>0) {
                if ($this->page > 0)
                        $html.= $this->link($this->linkBefore . ($this->page-1) . $this->linkAfter, false, $this->lblPrev);

                if ($this->page < $this->maxpage-1)  {
						if ($html!==false) {
							$html.=$this->sep;
						}
                        $html.= $this->link($this->linkBefore . ($this->page + 1) . $this->linkAfter, false,
 $this->lblNext);
				}
			}
			return $html;
    }
	
}