# Generic configuration items (to be used as interpolations) in other
# filters  or actions configurations
#

[INCLUDES]
# Load customizations if any available
before = common.conf

[Definition]

# Daemon definition is to be specialized (if needed) in .conf file
_daemon = \S*

# Common line prefixes (beginnings) which could be used in filters
#
#      [bsdverbose]? [hostname] [vserver tag] daemon_id spaces
#
# This can be optional (for instance if we match named native log files)
__prefix_line = %(known/__prefix_line)s(?:\w{7} )
