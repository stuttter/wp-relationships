# Relationships for WordPress

A WordPress plugin to provide an API and interface for relating many things to many other things in many ways.

This is basically one big and ambiguous join table, but with the flexibility to join users, posts, comments, taxonomy terms, groups, friends, messages, privacy, favorites, subscriptions, and all that stuff.

### Why not Posts to Posts?

The Posts to Posts plugin is a popular option for solving this problem. I reviewed it thoroughly, and found it lacking in several key areas:

* I want more than just posts to posts
* I want object & query caching
* I want a slick UI to make relating things super easy
* I want a simple API to register relationship types in PHP

### Give me a practical example?

Let's say you want to build some kind of privacy plugin. You want post authors to be able to block users from specific posts, and you want users to be able to block other users. You instantly have a many-to-many-to-many problem:

* Post authors need to block individual users
* Individual users may have already blocked post authors
* Individual posts may be hidden from other users

In addition to all this:

* Posts may be related to other posts in other ways
* Users may be friends, or grouped together, in ways that explicity allow access even if blocked
* RBAC is insufficient, and complex relationships are not possible within WordPress itself

To pull this off in an efficient manner, objects need a way to know which other objects they affect, and which other objects are affected by them, and objects may affect each other in multiple dimensions and ways.

Arguably, this is the responsibility of the application to determine, but it's immensely helpful if the database match those intentions without needing to write complex queries to do so.

### Why not just use taxonomies and terms?

The problem with taxonomy terms is that IDs between object types (users/posts/comments/terms) are not unique, so there is no way to tell the difference between post ID 5 and user ID 5, especially if you want to connect those 2 IDs together in multiple ways.

See: https://core.trac.wordpress.org/ticket/37686

Also, term relationships cannot currently have meta-data associated with them:

See: https://core.trac.wordpress.org/ticket/38265

# FAQ

### Does this create new database tables?

Yes. It adds `wp_relationships` and `wp_relationship_meta` to `$wpdb->tables`.

(If you use a database drop-in for high-availability, you will need to define these tables yourself.)

### Does this support object caching?

Yes. It uses a `relationships` cache-group for all relationship objects.

### Does this modify existing database tables?

No. All of the WordPress core database tables remain untouched.

### Where can I get support?

You probably can't right now. This is still in development and lots of stuff is going to change, so being self-sufficient and keeping up with breaking changes is still pretty important.

### Can I contribute?

Oh, heavens yes. Pull requests, issues, criticisms, and forks are welcome and encouraged.