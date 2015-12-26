<?php

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Ytake\LaravelCouchbase\Exceptions;

/**
 * Class NotSupportedException.
 */
class NotSupportedException extends \Exception
{
    /**
     * @param array           $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct("$message method is not supported", $code, $previous);
    }
}
