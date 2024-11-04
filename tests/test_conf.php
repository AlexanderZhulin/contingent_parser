<?php
class A {
    public $a;
    
    public function y() {
        $this->a = new class {
        public readonly string $x;
        
        public function d(): void {
            $this->x = '1';
        } 
    };
        $this->a->d();
        echo $this->a->x;
    }
}

$a = new A();
$a->y();