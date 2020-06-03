<?php

namespace Preview;

use function Pre\Plugin\instance;
use function Pre\Phpx\Html\supports;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;

class PreviewCompiler extends Compiler implements CompilerInterface
{
    /**
     * Compile the view at the given path.
     *
     * @param  string|null  $path
     * @return void
     */
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        if (!is_null($this->cachePath)) {
            $contents = $this->compileString(
                $this->files->get($this->getPath())
            );

            if (!empty($this->getPath())) {
                $tokens = $this->getOpenAndClosingPhpTokens($contents);

                // If the tokens we retrieved from the compiled contents have at least
                // one opening tag and if that last token isn't the closing tag, we
                // need to close the statement before adding the path at the end.
                if ($tokens->isNotEmpty() && $tokens->last() !== T_CLOSE_TAG) {
                    $contents .= ' ?>';
                }

                $contents .= "<?php /**PATH {$this->getPath()} ENDPATH**/ ?>";
            }

            $this->files->put(
                $this->getCompiledPath($this->getPath()),
                $contents
            );
        }
    }

    /**
     * Get the open and closing PHP tag tokens from the given string.
     *
     * @param  string  $contents
     * @return \Illuminate\Support\Collection
     */
    protected function getOpenAndClosingPhpTokens($contents)
    {
        return collect(token_get_all($contents))
            ->pluck($tokenNumber = 0)
            ->filter(function ($token) {
                return in_array($token, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG]);
            });
    }

    /**
     * Get the path currently being compiled.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path currently being compiled.
     *
     * @param  string  $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Compile the given Pre template contents.
     *
     * @param  string  $value
     * @return string
     */
    public function compileString($value)
    {
        $parser = instance();
        $result = $parser->format($parser->parse($value));
        $code = $this->resolveComponentsToClasses($result);
        return $code;
    }

    protected function resolveComponentsToClasses($code)
    {
        $tokens = token_get_all($code);

        $code = "";
        $isFunction = false; // hold when there's a defined function/method
        $isRenderFunction = false; // set when there's render function is met

        foreach ($tokens as $token) {
            if(is_array($token)) {
                [$id, $content] = $token;

                if($id == T_EMPTY || $content == " "){
                    $code .= $content;
                    continue;
                }

                if($id == T_FUNCTION){
                    $isFunction = true;
                    $code .= $content;
                    continue;
                }

                if ($isFunction && ($content == "render" || $content !== "render")) {
                    $isFunction = false;
                    $code .= $content;
                    continue;
                }

                if($content == 'render') {
                    $isRenderFunction = true;
                    continue;
                }

                if($isRenderFunction == true && $id == T_CONSTANT_ENCAPSED_STRING) {
                    $content = trim($content, '"');
                    if (supports($content) || strpos($content, '\\')) {
                        $code .= "render(\"$content\"";
                    } else {
                        $class = $content . '::class';
                        $code .= "render($class";
                    }
                    $isRenderFunction = false;
                    continue;
                }

                $code .= $content;
                continue;
            }

            if(!$isRenderFunction) {
                $code .= $token;
            }
        }

        return $code;
    }
}
