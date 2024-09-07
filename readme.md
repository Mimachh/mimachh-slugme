Deux façon de l'utiliser : 
- une fonction simple qui permettra de generer un slug à partir d'une data et qui renverra le slug.


Le package ne va pas fournir de migration.
Il faudra ajouter la column manuellement 


# Slugme

`Slugme` is a simple and efficient Laravel package that helps you automatically generate unique slugs for a specified attribute of your models and store them in the database.

## Installation

To install the package via Composer, run the following command:

```php
composer require mimachh/slugme
```

## Usage

### Important: Manually Add the `slug` Column

Please note that the `slug` column must be manually added in your database migration.

### Implement the Sluggable Interface in Your Model
To use this package, you need to implement the Sluggable interface in the model you want to slugify.

```php
use Mimachh\Slugme\Contracts\Sluggable;
use Mimachh\Slugme\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements Sluggable
{
    use HasSlug;

    // Define the column to store the slug
    public function slugColumn(): string {
        return 'slug';
    }

    // Define the attribute to generate the slug from
    public function slugAttribute(): string {
        return 'title';
    }
}
```

### Automatically Generate Slugs
Once you’ve implemented the Sluggable interface and used the HasSlug trait, slugs will be automatically generated when creating or updating the model. The package ensures that slugs are unique, even when there are conflicts.

For example, if the title is My First Post, the package will generate a slug like `my-first-post`. If the slug already exists, it will append a counter (e.g., `my-first-post-1`).

### Update Slugs on Model Updates
If you update the slugAttribute (e.g., title), the package will automatically regenerate and ensure that the slug remains unique.

### Custom Slug Logic
You can also customize the slug generation logic by overriding the generateUniqueSlug method in your model if needed.

```php
public static function generateUniqueSlug(string $attribute): string {
    // Custom slug generation logic here
}
```
Example
Here's an example of how to create and update a model with slugs:

```php
$post = new Post();
$post->title = 'My Awesome Post';
$post->save(); // This will automatically generate a slug and save it in the `slug` column.

$post->title = 'My Updated Awesome Post';
$post->save(); // This will update the slug accordingly.
```
## Generating Unique Slugs without Implementing the `Sluggable` Interface

If you prefer not to implement the `Sluggable` interface and want to manually handle slug generation, you can use the `SlugGenerator` service included with this package. This approach gives you the flexibility to call the slug generation method directly and handle saving it to the database yourself.

### 1. Using the `SlugGenerator` Service

The `SlugGenerator` class allows you to generate unique slugs for any model and attribute, while ensuring the slug is unique, even during model updates.

Here’s how you can use it.

#### Example of Creating a Slug

In this example, we’ll generate a unique slug for a `Post` model based on the `title` attribute.

```php
use Mimachh\Slugme\Services\SlugGenerator;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'slug'];

    public static function boot()
    {
        parent::boot();

        // Automatically generate a unique slug when creating a new model
        static::creating(function ($post) {
            $post->slug = SlugGenerator::generateUniqueSlug($post->title, Post::class);
        });

        // Automatically regenerate the slug if the title is updated
        static::updating(function ($post) {
            if ($post->isDirty('title')) {
                $post->slug = SlugGenerator::generateUniqueSlug($post->title, Post::class, $post->id);
            }
        });
    }
}
```
#### In this example:

When a new Post is created, the creating event automatically generates a slug using the title attribute.
During an update, if the title has been modified, the updating event regenerates the slug while ensuring uniqueness, excluding the current post's ID.
### 2. Manual Slug Generation
You can also generate slugs manually in your controller or anywhere in your application. This gives you control over when and where slugs are generated and saved.

```php
use Mimachh\Slugme\Services\SlugGenerator;

// Creating a new post with manual slug generation
$post = new Post();
$post->title = 'My Awesome Post';
$post->slug = SlugGenerator::generateUniqueSlug($post->title, Post::class); // Generate slug manually
$post->save();
```

#### For updates:

```php
$post = Post::find(1);
$post->title = 'Updated Post Title';
$post->slug = SlugGenerator::generateUniqueSlug($post->title, Post::class, $post->id); // Pass the current ID to exclude it
$post->save();
```

#### 3. Explanation of the SlugGenerator
The `SlugGenerator::generateUniqueSlug` method takes the following parameters:

- `string $attribute`: The attribute (e.g., title) you want to generate the slug from.
- `string $modelClass`: The model class (e.g., Post::class) to check for slug uniqueness.
- `int|null $currentId`: (Optional) The ID of the current model, used to exclude this record during updates.
- `string $slugColumn`: (Optional) The column in the database where the slug is stored (defaults to slug).

#### Example of a Migration
As mentioned earlier, make sure to add the slug column to your migration, like so:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // Add this column
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
```

With this approach, you have full control over slug generation and uniqueness checks, without the need to implement the Sluggable interface or use traits.
