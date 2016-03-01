#Using these Rules and Functions to create a Liturgical Calendar for the Latin Rite of the Roman Catholic Church.

*Using CLIPS v. 6.24.*

The CLIPS function `(string-to-field)` does not appear to be present in all versions of CLIPS tested. In order to convert strings to integers, a utility function `(string-to-integer)` has been developed; it can found in the file `RomanFuncs03.clp`.

This substitution of the function `(string-to-field)` has allowed these CLIPS files to work with Tcl CLIPS package \(v. 1.0\) and the PyCLIPS package, which works on Python 2.7.6, but not Python 3.

As at 29 August 2014, the Node.js CLIPS package dumps core when executing the second `(run)` command.
