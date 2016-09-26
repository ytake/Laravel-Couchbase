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

namespace Ytake\LaravelCouchbase\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Ytake\LaravelCouchbase\Schema\Builder;
use Ytake\LaravelCouchbase\Query\Builder as QueryBuilder;
use Ytake\LaravelCouchbase\Schema\Blueprint as CouchbaseBlueprint;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;

/**
 * Class CouchbaseMigrationRepository
 * @codeCoverageIgnore
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseMigrationRepository extends DatabaseMigrationRepository
{
    /**
     * {@inheritdoc}
     */
    public function log($file, $batch)
    {
        $record = ['migration' => $file, 'batch' => $batch];

        $builder = $this->table();
        if ($builder instanceof QueryBuilder) {
            /** @var Builder */
            $builder->key("{$file}:{$batch}")->insert($record);

            return;
        }
        $builder->insert($record);
    }

    /**
     * {@inheritdoc}
     */
    public function createRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        if ($schema instanceof Builder) {
            $schema->create($this->table, function (CouchbaseBlueprint $table) {
                $table->primaryIndex();
                $table->index(['migration', 'batch'], 'migration_secondary_index');
            });

            return;
        }
        $schema->create($this->table, function (Blueprint $table) {
            $table->string('migration');
            $table->integer('batch');
        });
    }
}
