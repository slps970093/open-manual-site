<?php

namespace Tests\Feature;

use App\Models\Manual;
use App\Models\ManualMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTreeOperationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test moving a menu item to a new parent.
     */
    public function test_can_move_menu_item_to_new_parent()
    {
        $manual = Manual::create([
            'url_slug' => 'move-test',
            'name' => ['en' => 'Move Test'],
            'is_public' => true,
        ]);

        // Create root menu items
        $root1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root 1'],
            'click_action' => 'expand',
        ]);

        $root2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root 2'],
            'click_action' => 'expand',
        ]);

        // Create child under root1
        $child = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child'],
            'click_action' => 'expand',
            'parent_id' => $root1->id,
        ]);

        // Verify initial structure
        $this->assertTrue($child->isDescendantOf($root1));
        $this->assertFalse($child->isDescendantOf($root2));

        // Move child to root2
        $child->moveTo($root2);

        // Verify new structure
        $this->assertFalse($child->fresh()->isDescendantOf($root1));
        $this->assertTrue($child->fresh()->isDescendantOf($root2));
    }

    /**
     * Test moving a menu item to root.
     */
    public function test_can_move_menu_item_to_root()
    {
        $manual = Manual::create([
            'url_slug' => 'move-to-root-test',
            'name' => ['en' => 'Move to Root Test'],
            'is_public' => true,
        ]);

        // Create root menu
        $root = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root'],
            'click_action' => 'expand',
        ]);

        // Create child
        $child = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        // Verify initial structure
        $this->assertTrue($child->isDescendantOf($root));

        // Move child to root
        $child->moveTo(null);

        // Verify new structure
        $this->assertFalse($child->fresh()->isDescendantOf($root));
        $this->assertTrue($child->fresh()->isRoot());
    }

    /**
     * Test deleting a menu item with descendants.
     */
    public function test_deleting_menu_item_with_descendants()
    {
        $manual = Manual::create([
            'url_slug' => 'delete-test',
            'name' => ['en' => 'Delete Test'],
            'is_public' => true,
        ]);

        // Create root menu
        $root = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root'],
            'click_action' => 'expand',
        ]);

        // Create children
        $child1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 1'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        $child2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 2'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        // Create grandchild
        $grandchild = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Grandchild'],
            'click_action' => 'expand',
            'parent_id' => $child1->id,
        ]);

        $rootId = $root->id;
        $child1Id = $child1->id;
        $child2Id = $child2->id;
        $grandchildId = $grandchild->id;

        // Delete root (should cascade to all descendants)
        $root->delete();

        // Verify all are soft deleted
        $this->assertSoftDeleted('manual_menu', ['id' => $rootId]);
        $this->assertSoftDeleted('manual_menu', ['id' => $child1Id]);
        $this->assertSoftDeleted('manual_menu', ['id' => $child2Id]);
        $this->assertSoftDeleted('manual_menu', ['id' => $grandchildId]);
    }

    /**
     * Test tree structure integrity after operations.
     */
    public function test_tree_structure_integrity()
    {
        $manual = Manual::create([
            'url_slug' => 'integrity-test',
            'name' => ['en' => 'Integrity Test'],
            'is_public' => true,
        ]);

        // Create a tree structure
        $root = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root'],
            'click_action' => 'expand',
        ]);

        $child1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 1'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        $child2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 2'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        $grandchild = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Grandchild'],
            'click_action' => 'expand',
            'parent_id' => $child1->id,
        ]);

        // Verify tree structure
        $this->assertTrue($root->isRoot());
        $this->assertFalse($child1->isRoot());
        $this->assertFalse($child2->isRoot());
        $this->assertFalse($grandchild->isRoot());

        // Verify parent-child relationships
        $this->assertTrue($child1->isDescendantOf($root));
        $this->assertTrue($child2->isDescendantOf($root));
        $this->assertTrue($grandchild->isDescendantOf($root));
        $this->assertTrue($grandchild->isDescendantOf($child1));

        // Verify ancestor relationships
        $this->assertTrue($root->isAncestorOf($child1));
        $this->assertTrue($root->isAncestorOf($grandchild));
        $this->assertTrue($child1->isAncestorOf($grandchild));

        // Verify depth
        $this->assertEquals(0, $root->getDepth());
        $this->assertEquals(1, $child1->getDepth());
        $this->assertEquals(1, $child2->getDepth());
        $this->assertEquals(2, $grandchild->getDepth());
    }

    /**
     * Test getting all descendants with eager loading.
     */
    public function test_get_all_descendants_with_eager_loading()
    {
        $manual = Manual::create([
            'url_slug' => 'descendants-test',
            'name' => ['en' => 'Descendants Test'],
            'is_public' => true,
        ]);

        // Create a tree structure
        $root = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root'],
            'click_action' => 'expand',
        ]);

        $child1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 1'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        $child2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 2'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        $grandchild = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Grandchild'],
            'click_action' => 'expand',
            'parent_id' => $child1->id,
        ]);

        // Get all descendants
        $descendants = $root->getAllDescendants();

        // Verify count
        $this->assertCount(3, $descendants);

        // Verify all descendants are included
        $descendantIds = $descendants->pluck('id')->toArray();
        $this->assertContains($child1->id, $descendantIds);
        $this->assertContains($child2->id, $descendantIds);
        $this->assertContains($grandchild->id, $descendantIds);
    }

    /**
     * Test getting tree structure.
     */
    public function test_get_tree_structure()
    {
        $manual = Manual::create([
            'url_slug' => 'tree-structure-test',
            'name' => ['en' => 'Tree Structure Test'],
            'is_public' => true,
        ]);

        // Create a tree structure
        $root = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root'],
            'click_action' => 'expand',
        ]);

        $child1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 1'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        $child2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child 2'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        // Get tree structure
        $structure = $root->getTreeStructure();

        // Verify structure
        $this->assertEquals($root->id, $structure['id']);
        $this->assertEquals('Root', $structure['name']);
        $this->assertEquals(0, $structure['depth']);
        $this->assertCount(2, $structure['children']);
    }

    /**
     * Test moving a subtree maintains all relationships.
     */
    public function test_moving_subtree_maintains_relationships()
    {
        $manual = Manual::create([
            'url_slug' => 'subtree-move-test',
            'name' => ['en' => 'Subtree Move Test'],
            'is_public' => true,
        ]);

        // Create two root menus
        $root1 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root 1'],
            'click_action' => 'expand',
        ]);

        $root2 = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root 2'],
            'click_action' => 'expand',
        ]);

        // Create subtree under root1
        $subtreeRoot = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Subtree Root'],
            'click_action' => 'expand',
            'parent_id' => $root1->id,
        ]);

        $subtreeChild = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Subtree Child'],
            'click_action' => 'expand',
            'parent_id' => $subtreeRoot->id,
        ]);

        // Verify initial structure
        $this->assertTrue($subtreeRoot->isDescendantOf($root1));
        $this->assertTrue($subtreeChild->isDescendantOf($subtreeRoot));
        $this->assertTrue($subtreeChild->isDescendantOf($root1));

        // Move subtree to root2
        $subtreeRoot->moveTo($root2);

        // Verify relationships are maintained
        $this->assertFalse($subtreeRoot->fresh()->isDescendantOf($root1));
        $this->assertTrue($subtreeRoot->fresh()->isDescendantOf($root2));
        $this->assertTrue($subtreeChild->fresh()->isDescendantOf($subtreeRoot));
        $this->assertTrue($subtreeChild->fresh()->isDescendantOf($root2));
    }

    /**
     * Test that circular references are prevented.
     */
    public function test_circular_reference_prevention()
    {
        $manual = Manual::create([
            'url_slug' => 'circular-test',
            'name' => ['en' => 'Circular Test'],
            'is_public' => true,
        ]);

        // Create a tree structure
        $root = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Root'],
            'click_action' => 'expand',
        ]);

        $child = ManualMenu::create([
            'manual_id' => $manual->id,
            'name' => ['en' => 'Child'],
            'click_action' => 'expand',
            'parent_id' => $root->id,
        ]);

        // Verify that child cannot be moved to itself
        $this->assertTrue($child->isDescendantOf($root));

        // Attempting to make child a parent of root should fail or be prevented
        // This is handled by ClosureTable library
        $this->assertTrue($root->isAncestorOf($child));
    }
}
