<?php
namespace {

	if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
		echo 'Warning: Infection may only be invoked from a command line', PHP_EOL;
	}

	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/vendor/infection/infection/app/bootstrap.php';
}
namespace Infection\Finder {

	use Infection\Finder\Exception\FinderException;
	use Symfony\Component\Process\ExecutableFinder;
	use Symfony\Component\Process\Process;

	/** @noinspection PhpUndefinedClassInspection */
	class TestFrameworkFinder extends AbstractExecutableFinder
	{
		/**
		 * @var string
		 */
		private $testFrameworkName;

		/**
		 * @var string
		 */
		private $customPath;

		/**
		 * @var string
		 */
		private $cachedPath;

		public function __construct(string $testFrameworkName, string $customPath = null)
		{
			$this->testFrameworkName = $testFrameworkName;
			$this->customPath = $customPath;
		}

		public function find(): string
		{
			if ($this->cachedPath === null) {
				if (!$this->doesCustomPathExist()) {
					$this->addVendorFolderToPath();
				}

				$this->cachedPath = $this->findTestFramework();
			}

			return $this->cachedPath;
		}

		private function doesCustomPathExist(): bool
		{
			return $this->customPath && file_exists($this->customPath);
		}

		private function addVendorFolderToPath()
		{
			$vendorPath = null;

			try {
				$process = new Process(sprintf('%s %s', $this->findComposer(), 'config bin-dir'));
				$process->run();
				$vendorPath = trim($process->getOutput());
			} catch (\RuntimeException $e) {
				$candidate = getcwd() . '/vendor/bin';
				if (file_exists($candidate)) {
					$vendorPath = $candidate;
				}
			}

			if (null !== $vendorPath) {
				putenv('PATH=' . $vendorPath . PATH_SEPARATOR . getenv('PATH'));
			}
		}

		private function findComposer(): string
		{
			return (new ComposerExecutableFinder())->find();
		}

		private function findTestFramework(): string
		{
			if ($this->doesCustomPathExist()) {
				return $this->customPath; // Bug fixed.
			}

			$candidates = [$this->testFrameworkName, $this->testFrameworkName . '.phar'];
			$finder = new ExecutableFinder();

			foreach ($candidates as $name) {
				if ($path = $finder->find($name, null, [getcwd()])) {
					return $path;
				}
			}

			$path = $this->searchNonExecutables($candidates, [getcwd()]);

			if (null !== $path) {
				return $path;
			}

			throw FinderException::testFrameworkNotFound($this->testFrameworkName);
		}
	}
}
namespace {

	use Infection\Console\Application;
	use Symfony\Component\Console\Input\ArgvInput;

	function prepareArgv()
	{
		$argv = $_SERVER['argv'];

		$found = false;

		while (next($argv)) {
			$value = current($argv);
			if (!$value || '-' !== $value[0]) {
				$found = true;
			}
		}

		if (!$found) {
			array_splice($argv, 1, 0, 'run');
		}

		return $argv;
	}

	$input = new ArgvInput(prepareArgv());

	$application = new Application($container ?? null);
    /** @noinspection PhpUnhandledExceptionInspection */
    $application->run($input);
}