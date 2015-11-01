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

use Illuminate\Support\ServiceProvider;

/**
 * Class CompileServiceProvider
 */
class CompileServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public static function compiles()
    {
        return [
            base_path() . '/vendor/ytake/laravel-couchbase/src/Cache/CouchbaseStore.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Database/CouchbaseConnection.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Database/CouchbaseConnector.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Exceptions/FlushException.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Exceptions/NotSupportedException.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Query/Grammer.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Query/Processor.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/CouchbaseServiceProvider.php',
        ];
    }
}
