# VanillaGenerator
This is a port of [GlowstoneMC](https://github.com/GlowstoneMC/Glowstone)'s world generator.
VanillaGenerator will register two world generator types when it loads — `vanilla_overworld` and `vanilla_nether`. Set the generator type of your world to any one of these to try them out.
This can be done directly from `pocketmine.yml` like so:
```yaml
worlds:
 world:
  generator: vanilla_overworld # sets generator type of the world with folder name "world" to "vanilla_generator"
 nether:
  generator: vanilla_nether
```
You _may_ be required to delete your existing world for the generator type to change.
