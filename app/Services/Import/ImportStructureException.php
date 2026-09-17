<?php

namespace App\Services\Import;

use RuntimeException;

/** Il file non e' elaborabile: foglio assente, colonna mancante, file illeggibile. */
class ImportStructureException extends RuntimeException {}
