# Doctrine Collections Fix Example

This repository is based on [the StackOverflow thread](https://stackoverflow.com/questions/13623285/doctrine-self-referencing-entity-disable-fetching-of-children). By default, Doctrine will trigger an additional database query each time you get another collection of children via the dedicated property. The original solution and my enhancement aim to fix the issue. See the tests for reference.

Check out my post on [Medium](https://medium.com/@franzose/optimizing-onetomany-doctrine-collections-398c782706a2?source=friends_link&sk=9b180ea975be65d7cc39fdb32685817d) or [Habr](https://habr.com/ru/post/715942/) (in Russian).
