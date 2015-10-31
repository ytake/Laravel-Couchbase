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

use CouchbaseClusterManager;

class Buckets
{
    /** @var CouchbaseClusterManager */
    protected $manager;

    /**
     * @param CouchbaseClusterManager $manager
     */
    public function __construct(CouchbaseClusterManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function bucket($name)
    {
        $result = $this->manager->listBuckets();
        foreach ($result as $row) {
            if ($row['name'] === $name) {
                return true;
            }
        }

        return false;
    }
}
