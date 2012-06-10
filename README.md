dgDebug
=======

**Quickly USAGE:

<?php
require('../_dgDebug/debug.php');

dbg()->d($varToDump, 'A title Here');

dbg()
	->d($varToDump, 'A title Here')
	->d($AnotherVarToDump, 'Another title Here')
;

**Description:

	Spanish/Castellano:

		dgDebug aporta interface sencilla para hacer debug en PHP. Permite ver 
	el contenido de una variable desde html con una interface de usuario comoda,
	al mismo tiempo se puede guardar un registro log de dichar variable. Ver
	los stacktrace en un punto concreto del codigo.
		
		Una de las aportaciones mas grandes en comparaci�n con respecto a 
	utilizar echo, var_dump, var_export, print_r y similares es que siempre 
	veremos en que linea se esta haciendo el dump. De forma que es mucho mas 
	sencillo eliminar

FEATURES