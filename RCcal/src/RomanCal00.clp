;####################################################################################
;Assumes version 6.24 of CLIPS (15 June 2006) as a minimum.
;;;;;; To print out the facts use: (printout t (facts) crlf) .

;;; This batch file should issue commands and load constructs as necessary.
;;;   Execute this script by (batch* "RomanCal00.clp") or (eval "(batch* \"RomanCal00.clp\")") .

;;;The script loads files of both rules and facts.
;;; The rules are managed by phases and within each phase by salience.
;;;  This subject matter is very hierarchical, and so needs more than usual control
;;;    over the order of facts on the agenda.

;;The following lines only required when script used in isolation, 
;; as they are present in the driving Python script.
;(clear)
;(reset)
;(defglobal ?*easter* = nil)
;(defglobal ?*calendarInUse* = "AU") ; could be nil, which means we only use the "GEN" calendar.
;(defglobal ?*EDM* = 3)
;(defglobal ?*yearSought* = 2016)

;;Batch script begins here.
(load "RomanGlobals01.clp")
(load "RomanTemplates01.clp")
(load "RomanFuncs01.clp")
(load "RomanFuncs02.clp")
(load "RomanRules01.clp")
(run) ; execute now to instantiate the unset globals, such as the date of Easter
(load "RomanFuncs03.clp")
(eval "(batch* \"CalendarGEN.clp\")")
(eval "(batch* \"CalendarOTHER.clp\")")
(eval "(batch* \"ReplacesInGen.clp\")")
(eval "(batch* \"MovedToSundays.clp\")")
(load "RomanRules02.clp")
(load "RomanRules03.clp")
(run)

;;;Test output
;(defglobal ?*sFileName* = (str-cat "harry" (random) ".txt"))
;(defglobal ?*sFileName* = (str-cat "harry" ".txt"))
;(save-facts ?*sFileName* local RCcalThisYear)
;(printout t "{'Result': " "'FINIS'}" crlf)

