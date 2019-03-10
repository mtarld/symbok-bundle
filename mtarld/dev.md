# Symbok - Dev
## TODOs
- [ ] Handle better exceptions
- [ ] Test custom and doctrine types namespaces
- [ ] Unit tests
- [ ] General configuration config yml (default nullable, default fluent)

## Nullable priorities

### Setter/Getter
1. Setter/Getter specific
2. Nullable annotation
3. Doctrine column
4. Setter/Getter general (data)
5. false

### Constructor
1. Nullable annotation
2. Doctrine column
3. Constructor general (data - allArgs)

## Fluent setters priorities
1. Setter/Getter specific
2. Setter/Getter general (data)

## Type priorities
1. @var
2. Doctrine
3. mixed
