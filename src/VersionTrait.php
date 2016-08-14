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

namespace Ytake\LaravelCouchbase;

/**
 * trait VersionTrait
 */
trait VersionTrait
{
    /**
     * @codeCoverageIgnore
     * @return bool
     */
    private function breakingVersion()
    {
        if (!str_contains(phpversion('couchbase'), 'beta')) {
            if (floatval(phpversion('couchbase')) >= 2.2) {
                return true;
            }
        }

        return false;
    }
}
