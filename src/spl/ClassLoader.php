<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

interface ClassLoader{
	public function __construct(ClassLoader $parent = null);
	public function addPath($path, $prepend = false);
	public function removePath($path);
	public function getClasses();
	public function getParent();
	public function register($prepend = false);
	public function loadClass($name);
	public function findClass($name);
}