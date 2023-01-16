# Doctrine Collections Fix Example

This repository is based on [the StackOverflow thread](https://stackoverflow.com/questions/13623285/doctrine-self-referencing-entity-disable-fetching-of-children). By default, Doctrine will trigger an additional database query each time you get another collection of children via the dedicated property. The original solution and my enhancement aim to fix the issue. See the tests for reference.
