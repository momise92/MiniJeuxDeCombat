<?php

class A
{
    public function appelerQuiEstCe()
    {
        static::quiEstCe();
    }
    
    public function quiEstCe() 
    {
        echo 'Je suis A';
    }
}

class B extends A
{
    public static function test()
    {
        parent::appelerQuiEstCe();
    }

    public function quiEstCe() 
    {
        echo 'Je suis B';
    }
}

class C extends B
{
    public function quiEstCe() 
    {
        echo 'Je suis C';
    }
}

C::test();