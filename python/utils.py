import datetime


def sToTimeFormat(s, format='%H:%M:%S.%f'):
    delta = datetime.timedelta(milliseconds=s * 1000)
    return (datetime.datetime.utcfromtimestamp(0) + delta).strftime(format)[:-3]
