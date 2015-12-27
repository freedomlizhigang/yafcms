<?php
class ErrorController extends Yaf\Controller_Abstract {
	public function errorAction($exception) {
	    // assert($exception === $exception->getCode());
	    $this->getView()->assign("code", $exception->getCode());
	    $this->getView()->assign("message", $exception->getMessage());
	    $this->getView()->assign("file", $exception->getFile());
	    $this->getView()->assign("line", $exception->getLine());
	    $this->getView()->display('error/error.html');
    }
}
?>