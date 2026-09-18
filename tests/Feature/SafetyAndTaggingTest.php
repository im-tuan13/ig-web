<?php
namespace Tests\Feature;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class SafetyAndTaggingTest extends TestCase { use RefreshDatabase;
 public function test_user_can_block_and_unblock_another_user(): void { $a=User::factory()->create(); $b=User::factory()->create(); $this->actingAs($a)->post(route('users.block',$b))->assertOk(); $this->assertDatabaseHas('user_blocks',['blocker_id'=>$a->id,'blocked_id'=>$b->id]); $this->actingAs($a)->delete(route('users.unblock',$b))->assertOk(); $this->assertDatabaseMissing('user_blocks',['blocker_id'=>$a->id,'blocked_id'=>$b->id]); }
 public function test_user_can_report_a_post(): void { $a=User::factory()->create(); $b=User::factory()->create(); $post=Post::factory()->create(['user_id'=>$b->id]); $this->actingAs($a)->postJson(route('reports.store'),['reportable_type'=>'post','reportable_id'=>$post->id,'reason'=>'spam'])->assertCreated(); $this->assertDatabaseHas('reports',['reporter_id'=>$a->id,'reportable_id'=>$post->id]); }
 public function test_owner_can_add_and_remove_coordinate_tag(): void { $a=User::factory()->create(); $b=User::factory()->create(); $post=Post::factory()->create(['user_id'=>$a->id]); $this->actingAs($a)->postJson(route('posts.tags.store',$post),['user_id'=>$b->id,'x_position'=>25,'y_position'=>50])->assertCreated(); $tag=$post->tags()->first(); $this->actingAs($a)->deleteJson(route('posts.tags.destroy',[$post,$tag]))->assertOk(); $this->assertDatabaseMissing('post_tags',['id'=>$tag->id]); }
}
