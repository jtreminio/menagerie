<?php

namespace m\Util;

use \m as m;

class PageTool {

	// values it needs to know to calculate the rest.
	public $Page;
	public $Limit;
	public $Total;
	public $LinkFormat;

	// values it can calculate.
	public $Offset;
	public $OffsetMax;
	public $PageCount;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function Update() {

		$this->PageCount = ceil($this->Total / $this->Limit);
		if($this->Page < 1) $this->Page = 1;
		if($this->Page > $this->PageCount) $this->Page = $this->PageCount;

		$this->Offset = (($this->Page - 1) * $this->Limit) + 1;
		if($this->Offset < 0) $this->Offset = 0;

		$this->OffsetMax = $this->Offset + $this->Limit;
		if($this->OffsetMax > $this->Total) $this->OffsetMax = $this->Total;

		return;
	}

	public function LinkFormat($page) {
		return str_replace('{PAGE}',$page,$this->LinkFormat);
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function Render() {
		$this->Update();

		ob_start();

		echo '<div class="pagetool">';
		echo "<div>Viewing {$this->Offset}-{$this->OffsetMax} of {$this->Total}</div>";
		echo '<div>';
			if($this->Page > 1)
			printf('<a href="%s">&laquo; Prev</a> | ',$this->LinkFormat($this->Page-1));
			else echo '&laquo; Prev | ';
		echo "Page {$this->Page} of {$this->PageCount}";
			if($this->Page < $this->PageCount)
			printf(' | <a href="%s">Next &raquo;</a>',$this->LinkFormat($this->Page+1));
			else echo ' | Next &raquo;';
		echo '</div>';
		echo '</div>';
		return ob_get_clean();
	}

}
