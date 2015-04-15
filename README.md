dgDebug 0.4.2
=============

Description:
------------

	Spanish/Castellano:

		dgDebug aporta interface sencilla para hacer debug en PHP. Permite ver 
	el contenido de una variable desde html con una interface de usuario comoda,
	al mismo tiempo se puede guardar un registro log de dichar variable. Ver
	los stacktrace en un punto concreto del codigo.
		
		Una de las aportaciones mas grandes en comparación con respecto a 
	utilizar echo, var_dump, var_export, print_r y similares es que siempre 
	veremos en que linea se esta haciendo el dump. De forma que es mucho mas 
	sencillo eliminar

FEATURES
--------

- Always you know when dump is.
- Debug Window easy to use.
- Minimize window (left click in window title)
- Maximize window (right click in window title)
- Collapse Blocks (left click in block title)
- Mark Blocs as viewed (right click in block title)
- Higlight Variables, Array, Object, String etc...
- Collapse Arrays in Arrays, Object in Arrays etc... (click on brace)

Quickly USAGE:
--------------

	<?php
	require('../_dgDebug/debug.php');

	dbg()->d($varToDump, 'A title Here');

	dbg()
		->d($varToDump, 'A title Here')
		->d($AnotherVarToDump, 'Another title Here')
	;

	/**
	* @since 0.4.1
	*/
	dbg('A block Title')
		->d($varToDump, 'A title Here')
		->d($AnotherVarToDump, 'Another title Here')
	;