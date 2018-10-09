<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace App\Util;

use ArrayIterator;
use Closure;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * Implements a collection that mutates the source array directly.
 *
 * NOTE: Implementation copied from Doctrine's ArrayCollection.
 */
class ProxyCollection implements Collection
{
    private $elements;

    public function __construct(array &$elements = [])
    {
        $this->elements = &$elements;
    }

    protected function createFrom(array $elements)
    {
        return new static($elements);
    }

    public function toArray()
    {
        return $this->elements;
    }

    public function first()
    {
        return reset($this->elements);
    }

    public function last()
    {
        return end($this->elements);
    }

    public function key()
    {
        return key($this->elements);
    }

    public function next()
    {
        return next($this->elements);
    }

    public function current()
    {
        return current($this->elements);
    }

    public function remove($key)
    {
        if ( ! isset($this->elements[$key]) && ! array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    public function removeElement($element)
    {
        $key = array_search($element, $this->elements, true);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! isset($offset)) {
            $this->add($value);
            return;
        }

        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    public function containsKey($key)
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    public function contains($element)
    {
        return in_array($element, $this->elements, true);
    }

    public function exists(Closure $p)
    {
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                return true;
            }
        }

        return false;
    }

    public function indexOf($element)
    {
        return array_search($element, $this->elements, true);
    }

    public function get($key)
    {
        return $this->elements[$key] ?? null;
    }

    public function getKeys()
    {
        return array_keys($this->elements);
    }

    public function getValues()
    {
        return array_values($this->elements);
    }

    public function count()
    {
        return count($this->elements);
    }

    public function set($key, $value)
    {
        $this->elements[$key] = $value;
    }

    public function add($element)
    {
        $this->elements[] = $element;

        return true;
    }

    public function isEmpty()
    {
        return empty($this->elements);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    public function map(Closure $func)
    {
        return $this->createFrom(array_map($func, $this->elements));
    }

    public function filter(Closure $p)
    {
        return $this->createFrom(array_filter($this->elements, $p));
    }

    public function forAll(Closure $p)
    {
        foreach ($this->elements as $key => $element) {
            if ( ! $p($key, $element)) {
                return false;
            }
        }

        return true;
    }

    public function partition(Closure $p)
    {
        $matches = $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [$this->createFrom($matches), $this->createFrom($noMatches)];
    }

    public function __toString()
    {
        return __CLASS__ . '@' . spl_object_hash($this);
    }

    public function clear()
    {
        $this->elements = [];
    }

    public function slice($offset, $length = null)
    {
        return array_slice($this->elements, $offset, $length, true);
    }
}
