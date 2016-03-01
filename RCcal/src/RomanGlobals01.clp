;####################################################################################
;;;;;; To print out the facts use: (printout t (facts) crlf) .

(defglobal ?*supreme-priority* = 500)
(defglobal ?*highest-priority* = 200) ; extraordinarily high priority
(defglobal ?*higher-priority* = 150)
(defglobal ?*high-priority* = 100) ; The default salience is 0 of a max 10,000 and min -10,000 .
(defglobal ?*med-high-priority* = 75) 
(defglobal ?*medium-priority* = 50) 
(defglobal ?*normal-priority* = 0)  ; The default priority for all rules.
(defglobal ?*lowish-priority* = -50)
(defglobal ?*med-low-priority* = -75)
(defglobal ?*low-priority* = -100)
(defglobal ?*lower-priority* = -150)
(defglobal ?*lowest-priority* = -200)
(defglobal ?*least-priority* = -500) ; The absolute lowest priority. Last rule to fire.
(defglobal ?*iEDM_JULIAN* = 1)
(defglobal ?*iEDM_ORTHODOX* = 2)
(defglobal ?*iEDM_WESTERN* = 3)
(defglobal ?*iFIRST_EASTER_YEAR* = 326)
(defglobal ?*iFIRST_VALID_GREGORIAN_YEAR* = 1583)
(defglobal ?*iLAST_VALID_GREGORIAN_YEAR* = 4099)
(defglobal ?*GENERAL_ROMAN_CALENDAR* = "GEN")