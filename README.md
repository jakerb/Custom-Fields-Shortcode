# Custom Fields Shortcode

Custom Fields Shortcode (CFS) plugin allows you to easily display custom fields into your WordPress pages whether it be through Advanced Custom Fields (ACF) or simple WordPress post meta. CFS also allows you to apply your custom filters to data being displayed as well as use a bunch of default ones.


## How to use
Let's say you have a new WordPress post that has a post meta field named `favourite_color` associated to it, its value is set to `orange`. Let's quickly show this information on the page; to do this simply add the following to your page content (this can be through the Gutenberg editor, Elementor or whatever editor plugin you use).

`[get_custom_field key="favourite_color"]`

Now, our page will display

```
orange
```


### Set Post ID

Easy right? The above will get the current post to find the favourite color value but what if we want to get the colour from another post? All we need to do is include the `id` parameter to specify which post we want to lookup.

`[get_custom_field key="favourite_color" id="256"]`

The above shortcode will look for the post meta within the post that has the id 256.

### Filters

This is where this plugin gets really interesting, you can also add your own filters to modify the meta information. Firstly, lets go through the anatomy of a filter and how we might use it, lets use our above example of using `favourite_color`.

`[get_custom_field key="favourite_color" filter="my_favourite_color"]`

Notice how we have a `filter` attribute with `my_favourite_color` as the value. We can now add a new filter to modify the text `orange` for our post, lets create that filter now.

```
add_filter('my_favourite_color', function($data) {

 $data->value = "My favourite color is {$data->value}";
 return $data;
});
```

Now, our page will display

```
My favourite color is orange
```

Cool, right? There are also a bunch of default filters included, take a look at these..

```
/* Date Formatting */
[get_custom_field key="birthday" date_format="D M j Y"]
```
> Sat Jan 01 2020

---

```
/* Change Text Case */
[get_custom_field key="favourite_color" text_format="lowercase"]
```
> orange

---

```
/* Change Text Case */
[get_custom_field key="favourite_color" text_format="uppercase"]
```
> ORANGE

---

```
/* Encode Text */
[get_custom_field key="favourite_color" text_format="md5"]
```
> fe01d67a002dfa0f3ac084298142eccd

---

```
/* Show if field contains value */
[get_custom_field key="favourite_color" text_contains="ora"]
```
> orange

---

```
/* Replace text */
[get_custom_field key="favourite_color" text_replace="orange,green"]
```
> green

---

## Use cases

### Elementor
Let's say that you're creating a site in Elementor and you have a loop template that lists out all posts under the category "Food", you set the loop template to the post title. This is the what the page would output.

**Hamburger**
**Hotdog**
**Beef Ramen**
**Chicken Kiev**
**Apple Pie**
**Toasted Bagel**

Now, let's say you want to include the sauce which is the custom meta field `sauce` within each post. As some of the food items do not have a sauce, you may end up with something like this..

**Hamburger**
Sauce: Ketchup

**Hotdog**
Sauce: Mustard

**Beef Ramen**
Sauce: 

**Chicken Kiev**
Sauce: 

**Apple Pie**
Sauce: Custard

**Toasted Bagel**
Sauce: 

As we can see from the example above, some posts such as Beef Ramen doesn't have a sauce associated with it so in order to hide the "Sauce" label we would have to add some extra functionality to check this.

Instead, we can simply create a filter to do this conditional logic really quickly. Let's edit the loop template so that it looks like the following:

```
<h1>{Post Title}</h1>
[get_custom_field key="sauce" filter="get_sauce"]
```

Notice that we've included the shortcode and specified that we want to get the key `sauce` and we haven't set the label "Sauce" anywhere in the template. We've also not specified a post ID because we will use the one within the loop and we're calling the filter `get_sauce`. Let's create a new filter with the `get_sauce` name and render out the page again.

```
add_filter('get_sauce', function($data) {
 /* Let's check if there is a sauce value */
 if(!empty($data->value)) {
  /* This post has a sauce, lets update the value.. */
  $data->value = "<p>Sauce: {$data->value}</p>";
 } else {
  $data->value = "<p>No Sauce</p>";
 }
 
 return $data;
});
```
Our page will now display..

**Hamburger**
Sauce: Ketchup

**Hotdog**
Sauce: Mustard

**Beef Ramen**
No Sauce

**Chicken Kiev**
No Sauce

**Apple Pie**
Sauce: Custard

**Toasted Bagel**
No Sauce

