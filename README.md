# Sitegeist.Monocle.ComponentExport

## Export FusionAst as JSON for standalone fusion-runtimes

This package exports the fusion ast and applies filters.

The command `./flow fusionexport:preset --preset styleguideItems` will export an fusion-ast with all
style-guide items and renderPathes for each item that will take all the props for the item from
the context.

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de
* Wilhelm Behncke - behncke@sitegeist.de

*The development and the public-releases of this package is generously sponsored
by our employer http://www.sitegeist.de.*

## Usage

The package defines two cli commands.

### `./flow fusionexport:list` 

```
List all configured fusion export configurations

COMMAND:
  sitegeist.monocle.componentexport:fusionexport:list

USAGE:
  ./flow fusionexport:list
```

### `./flow fusionexport:preset` 

```
Export the Fusion-AST as JSON, during export the filters defined in the given preset are applied

COMMAND:
  sitegeist.monocle.componentexport:fusionexport:preset

USAGE:
  ./flow fusionexport:preset [<options>]

OPTIONS:
  --package-key        site-package (defaults to the first found site-package)
  --preset             The preset-name for this export (defaults to 'default')
  --filename           The file to export the ast to, if no file is given the
                       result is returned directly.
```

### Preset `styleguideItems`

The package comes with a predefined filter chain 

## Configuration

This package is configured via Settings

```yaml
Sitegeist:
  Monocle:
    ComponentExport:

      #
      # The list of available export presets
      #
      presets:

        #
        # Plain fusion ast export without further processing
        #
        default:
          description: "Export the unmodified fusion ast"
          filters: []

        #
        # Export all styleguide-items plus their dependencies and create render pathes
        #
        styleguideItems:
          description: "Export all styleguide items and create render pathes"

          #
          # Fusion ast filter-chain
          #
          # Each filter has to implement \Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter\FusionAstFilterInterface
          # and can be configured with optional arguments.
          #
          # The order of the filters can be controlled via the optional `position` property
          #
          filters:
            filterPrototypes:
              position: 'start'
              class: \Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter\FilterPrototypes

              #
              # Definition of the Prototypes that shall be included in the export
              # All conditions that are given as arguments must be satisfied to include a prototype
              #
              arguments: &styleguidePprototypeListDefinition
                # Limit prototypes by fusion path that has to exist
                path: '__meta.styleguide'

                # Limit prototypes via pattern applied to the vendor name
                # vendor: 'Vendor.*'

                # Limit prototypes via pattern applied to the fusion name
                # name: 'Component.*'

            removeStyleguideProps:
              position: 'after filterPrototypes'
              class: \Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter\RemoveStyleguideProps

            createRenderPathes:
              position: 'end'
              class: \Sitegeist\Monocle\ComponentExport\Service\FusionAstFilter\CreateRenderPathes
              arguments: *styleguidePprototypeListDefinition
```

## Installation

THIS PACKAGE IS NOT YET PUBLISHED.

## Contribution

We will gladly accept contributions. Please send us pull requests.
