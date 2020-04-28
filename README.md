# VanillaGenerator
This is a port of [GlowstoneMC](https://github.com/GlowstoneMC/Glowstone)'s world generator. Before trying it out, back up your worlds.
The generator is not yet complete, not yet stable and it isn't hard to say that either.
To test the generator out, you can create a world with the generator name `vanilla_overworld` or `vanilla_nether`. Alternatively, edit your `pocketmine.yml` like so:
```yaml
worlds:
 world:
  generator: vanilla_overworld
 nether:
  generator: vanilla_nether
```

## PLEASE DON'T
create an "Incompatible API version" _error_ issue. This plugin is written for API 4.0.0. I will NOT be backporting it to API 3.0.0.
Yes, you are free to backport it yourself. No, I don't know when API 4.0.0 releases. The issue tracker is NOT a method to reach out to me regarding "Why" and "How" questions.
