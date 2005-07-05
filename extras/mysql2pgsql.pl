#!/usr/bin/perl -w

# MySQL to PostgreSQL dump file converter
#
# For usage: mysqldump --help
#
# Convert mysqldump file (from MySQL) to something readable by psql !
#
# homepage: http://www.rot13.org/~dpavlin/projects.html

# 1999-12-15 DbP -- Dobrica Pavlinusic <dpavlin@rot13.org>
# 1999-12-26 DbP don't make serial from auto_increment, create all manually
#		 (to set start value right)
# 2000-01-11 DbP now creates sequences with correct value
# 2000-04-25 DbP import into CVS (at cvs.linux.hr)
# 2001-01-29 tpo -- Tomas Pospisek <tpo@sourcepole.ch>:
#		 1) make script comply to usage:
#		 2) make script output to STDOUT instead of STERR
#		 3) change verbosity behaveour
#		 4) add debug option

# see rest of changelog at http://cvs.linux.hr/cvsweb.cgi/sql/mysql2pgsql

use DBI;

$DEBUG   =0;
$VERBOSE =0;

sub usage {
	print "Usage: $0 [-v|--h] mysql_dump_file [pg_database_name]\n\n";
	print "\t* mysql_dump_file is the filename of the mysql dump.\n";
	print "\t* pg_database_name is the name of the postgres database.\n";
	print "\t  When ommitted standard output is used.\n";
	print "\t  Combined with -v, it will tell you what SQL\n";
	print "\t  commands are being executed\n";
}

# if mysql_dump_file is not suplied or --h is supplied then we dump usage info
if (! defined($ARGV[0]) || $ARGV[0] eq "--h") {
	usage();
	exit 1;
}

if ($ARGV[0] eq "-v") {
	$VERBOSE = 1;
	# if verbose is set then user has to supply both arguments
	if(! defined($ARGV[1]) || ! defined($ARGV[2])) {
		usage();
		exit 1;
	} else {
		$dump="$ARGV[1]";
		$database="$ARGV[1]";
	}
} else {
	$dump="$ARGV[0]";

	# if database name is supplied then use it
	if (defined($ARGV[1])) {
		$database="$ARGV[1]";
	# else be verbose
	} else {
		$database="";
		$VERBOSE = 1;
	}
}

if ($database) {
	my $dbh = DBI->connect("DBI:Pg:dbname=template1","","") || die $DBI::errstr;
	$dbh->do("create database $database") || die $dbh->errstr();
	$dbh->disconnect;

	$dbh = DBI->connect("DBI:Pg:dbname=$database","","") || die $DBI::errstr;
}

$create=0;	# inside create table?
$table="";

open(DUMP,"$dump") || die "can't open dump file $dump";

while(<DUMP>) {
	chomp; s/\r//g;
	print "Processing line: $_\n" if $DEBUG;

# nuke comments or empty lines
	next if (/^#/ || /^$/);

	if ($create && /^\);/i) {	# end of create table squence
		$create=0;
		$sql =~ s/,$//g;	# strip last , inside create table
	}

	if ($create) {			# are we inside create table?

		# int,auto_increment -> serial
		if (/int.*auto_increment/i) {


			# this was simple solution, but squence isn't
			# initialized correctly so I have to do a work-around
			#
			# s/\w*int.*auto_increment/serial/ig;

			if (/^\s*(\w+)\s+/) {
				$seq="${table}_${1}_seq";
				push @sequences,"$table.$1";
				s/(\s+)\w*int.*auto_increment[^,]*/$1int4 default nextval('$seq') not null/ig;
			} else {
				die "can't get name of field!";
			}

		# int type conversion
		} elsif (/(\w*)int\(\d+\)/i) {
			$size=$1;
			$size =~ tr [A-Z] [a-z];
			if ($size eq "tiny" || $size eq "small") {
				$out = "int2";
			} elsif ($size eq "big") {
				$out = "int8";
			} else {
				$out = "int4";
			}
			s/\w*int\(\d+\)/$out/gc;
		}

		# nuke int unsigned
		s/(int\w+)\s+unsigned/$1/gi;

		# blob -> text
		s/\w*blob/text/gi;
		# tinytext/mediumtext -> text
		s/tinytext/text/gi;
		s/mediumtext/text/gi;

		# char -> varchar
		# PostgreSQL would otherwise pad with spaces as opposed
		# to MySQL! Your user interface may depend on this!
		s/\s+char/ varchar/gi;

		# nuke date representation (not supported in PostgreSQL)
		s/datetime default '[^']+'/datetime/i;
		s/date default '[^']+'/datetime/i;
		s/time default '[^']+'/datetime/i;

		# change not null datetime field to null valid ones
		# (to support remapping of "zero time" to null
		s/datetime not null/datetime/i;

		# nuke size of timestamp
		s/timestamp\([^)]*\)/timestamp/i;

		# double -> float8
		s/double\([^)]*\)/float8/i;

		# add unique to definition of type (MySQL separates this)
		if (/unique \w+ \((\w+)\)/i) {
			$sql=~s/($1)([^,]+)/$1$2 unique/i;
			next;
		}
		# FIX: unique for multipe columns (col1,col2) are unsupported!
		next if (/unique/i);

		# FIX: nuke keys
		next if (/^\s+key/i && !/^\s+primary key/i);

		# quote column names
		s/(^\s*)(\S+)(\s*)/$1"$2"$3/gi if (!/key/i);

		# remap colums with names of existing system attribute 
		if (/"oid"/i) {
			s/"oid"/"_oid"/g;
			print STDERR "WARNING: table $table uses column \"oid\" which is renamed to \"_oid\"\nYou should fix application manually! Press return to continue.";
			my $wait=<STDIN>;
		}
		s/oid/_oid/i if (/key/i && /oid/i); # fix oid in key

	} else {	# not inside create table

		#---- fix data in inserted data: (from MS world)
		# FIX: disabled for now
		if (00 && /insert into/i) {
			s!\x96!-!g;	# --
			s!\x93!"!g;	# ``
			s!\x94!"!g;	# ''
			s!\x85!... !g;	# \ldots
			s!\x92!`!g;
		}

		# fix dates '0000-00-00 00:00:00' (should be null)
		s/'0000-00-00 00:00:00'/null/gi;
		s/'0000-00-00'/null/gi;
		s/'00:00:00'/null/gi;
		s/([12]\d\d\d)([01]\d)([0-3]\d)([0-2]\d)([0-6]\d)([0-6]\d)/'$1-$2-$3 $4:$5:$6'/;

		# protect ; in inserts
		while (/('[^']*);([^']*)'/) {
			s/('[^']*);([^']*')/$1 _dotcol_ $2/g;
		}
	}

	$sql.="$_";

	if (/create table/i) {
		$create++;
		/create table (\w+)/i;
		$table=$1 if (defined($1));
	}



	if ($sql=~/\);/) {
		($dosql,$sql)=split(/\);/,$sql);
		$dosql.=");";	# nuked by split, put it back!
		if ("$dosql" ne "") {
			$dosql=~s/ _dotcol_ /;/g;
			print "$dosql\n" if $VERBOSE;
			if($database) {
				$dbh->do("$dosql") || die "do: '$dosql' ",$dbh->errstr();
			}
		} else {
			print STDERR "empty sql!\n";
		}
	}

}

#print "creating sequences: @sequences\n";

foreach $seq (@sequences) {
	($table,$field) = split(/\./,$seq);

	$sql="select max($field)+1 from $table";
	print "$sql\n" if $VERBOSE;
	if($database){
		$sth = $dbh->prepare($sql) || die $dbh->errstr();
		$sth->execute() || die $sth->errstr();
		($start) = $sth->fetchrow_array() || 1;
	} else {
		print STDERR<<EOT
WARNING: Couldn't find the sequence start for the field $field
         in table $table since you didn't give me an accessible DB.
	 Please verify the validity of the SQL command before inserting
	 into your DB.
EOT
;
		$start = 1;
	}
	$seq="${table}_${field}_seq";

	$sql="create sequence $seq start $start increment 1";
	print "$sql\n" if $VERBOSE;
	if($database){
		$dbh->do("create sequence $seq start $start increment 1") || die $dbh->errstr();
	}
}

print "\n";	
